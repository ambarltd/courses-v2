<?php

declare(strict_types=1);

namespace Galeas\Api\Service\EventStore;

use Doctrine\DBAL\Connection;
use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\Event\EventMapper;
use Galeas\Api\Common\Event\SerializedEvent;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Service\EventStore\Exception\CancellingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\CompletingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\FindingAggregateRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\SavingEventRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\SQLError;
use Galeas\Api\Service\EventStore\Exception\TransactionIsAlreadyActive;

class SQLEventStore implements EventStore
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(SQLEventStoreConnection $SQLEventStoreConnection)
    {
        $this->connection = $SQLEventStoreConnection->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): void
    {
        try {
            if ($this->connection->isTransactionActive()) {
                throw new TransactionIsAlreadyActive();
            }

            $this->connection->beginTransaction();
        } catch (\Throwable $exception) {
            throw new EventStoreCannotWrite($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function completeTransaction(): void
    {
        try {
            if (false === $this->connection->isTransactionActive()) {
                throw new CompletingTransactionRequiresActiveTransaction();
            }

            $this->connection->commit();
        } catch (\Throwable $exception) {
            throw new EventStoreCannotWrite($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cancelTransaction(): void
    {
        try {
            if (false === $this->connection->isTransactionActive()) {
                throw new CancellingTransactionRequiresActiveTransaction();
            }

            $this->connection->rollBack();
        } catch (\Throwable $exception) {
            throw new EventStoreCannotWrite($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $aggregateId): ?Aggregate
    {
        try {
            if (false === $this->connection->isTransactionActive()) {
                throw new FindingAggregateRequiresActiveTransaction();
            }

            $statement = $this->connection->prepare('SELECT * FROM `event` WHERE `aggregate_id` = ? FOR UPDATE');
            $statement->bindValue(1, $aggregateId);

            $statementExecutedSuccessfully = $statement->execute();
            if (!$statementExecutedSuccessfully) {
                throw new SQLError(sprintf('Database error with code "%s". Error info in JSON format: "%s"', $statement->errorCode(), json_encode($statement->errorInfo())));
            }

            $eventArrays = $statement->fetchAll();
            if (0 === count($eventArrays)) {
                return null;
            }

            $aggregateEvents = array_map(function (array $eventArray) {
                return SerializedEvent::fromProperties(
                    $eventArray['event_id'],
                    $eventArray['aggregate_id'],
                    $eventArray['authorizer_id'],
                    $eventArray['source_event_id'],
                    $eventArray['event_occurred_on'],
                    $eventArray['event_name'],
                    $eventArray['json_payload'],
                    $eventArray['json_metadata']
                );
            }, $eventArrays);

            $creationEvent = array_shift($aggregateEvents);
            $transformationEvents = $aggregateEvents;

            return EventMapper::aggregateFromEvents(
                EventMapper::serializedEventsToEvents([$creationEvent])[0],
                EventMapper::serializedEventsToEvents($transformationEvents)
            );
        } catch (\Throwable $exception) {
            throw new EventStoreCannotRead($exception);
        }
    }

    public function findEvent(string $eventId): ?Event
    {
        // TODO: Implement findEvent() method.
    }


    /**
     * {@inheritdoc}
     */
    public function save(Event $event): void
    {
        try {
            if (false === $this->connection->isTransactionActive()) {
                throw new SavingEventRequiresActiveTransaction();
            }

            $serializedEvent = EventMapper::eventsToSerializedEvents([$event])[0];

            $this->connection->insert('event',
                [
                    'event_id' => $serializedEvent->eventId(),
                    'aggregate_id' => $serializedEvent->aggregateId(),
                    'authorizer_id' => $serializedEvent->authorizerId(),
                    'source_event_id' => $serializedEvent->sourceEventId(),
                    'event_occurred_on' => $serializedEvent->eventOccurredOn(),
                    'event_name' => $serializedEvent->eventName(),
                    'json_payload' => $serializedEvent->jsonPayload(),
                    'json_metadata' => $serializedEvent->jsonMetadata(),
                ],
                [
                    'event_id' => \PDO::PARAM_STR,
                    'aggregate_id' => \PDO::PARAM_STR,
                    'authorizer_id' => $serializedEvent->authorizerId() ? \PDO::PARAM_STR : \PDO::PARAM_NULL,
                    'source_event_id' => $serializedEvent->sourceEventId() ? \PDO::PARAM_STR : \PDO::PARAM_NULL,
                    'event_occurred_on' => \PDO::PARAM_STR,
                    'event_name' => \PDO::PARAM_STR,
                    'json_payload' => \PDO::PARAM_LOB,
                    'json_metadata' => \PDO::PARAM_LOB,
                ]
            );
        } catch (\Throwable $exception) {
            throw new EventStoreCannotWrite($exception);
        }
    }
}

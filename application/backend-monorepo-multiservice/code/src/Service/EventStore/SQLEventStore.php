<?php

declare(strict_types=1);

namespace Galeas\Api\Service\EventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Galeas\Api\Common\Event\AggregateFromEvents;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\Event\EventDeserializer;
use Galeas\Api\Common\Event\EventSerializer;
use Galeas\Api\Common\Event\SerializedEvent;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\Service\EventStore\Exception\CancellingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\CompletingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\FindingAggregateRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\SavingEventRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\TransactionIsAlreadyActive;

class SQLEventStore implements EventStore
{
    private Connection $connection;

    private string $eventStoreTableName;

    public function __construct(SQLEventStoreConnection $SQLEventStoreConnection, string $eventStoreTableName)
    {
        $this->connection = $SQLEventStoreConnection->getConnection();
        $this->eventStoreTableName = $eventStoreTableName;
    }

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

    public function find(string $aggregateId): ?AggregateAndEventIds
    {
        try {
            if (false === $this->connection->isTransactionActive()) {
                throw new FindingAggregateRequiresActiveTransaction();
            }

            $statement = $this->connection->prepare('SELECT * FROM '.$this->eventStoreTableName.' WHERE aggregate_id = ? FOR UPDATE');
            $statement->bindValue(1, $aggregateId);

            $eventArrays = $statement->executeQuery()->fetchAllAssociative();
            if (0 === \count($eventArrays)) {
                return null;
            }

            $aggregateEvents = array_map(static function (array $eventArray) use ($aggregateId) {
                if (
                    !\array_key_exists('event_id', $eventArray)
                    || !\is_string($eventArray['event_id'])
                    || !\array_key_exists('aggregate_id', $eventArray)
                    || !\is_string($eventArray['aggregate_id'])
                    || !\array_key_exists('aggregate_version', $eventArray)
                    || !\is_int($eventArray['aggregate_version'])
                    || !\array_key_exists('causation_id', $eventArray)
                    || !\is_string($eventArray['causation_id'])
                    || !\array_key_exists('correlation_id', $eventArray)
                    || !\is_string($eventArray['correlation_id'])
                    || !\array_key_exists('recorded_on', $eventArray)
                    || !\is_string($eventArray['recorded_on'])
                    || !\array_key_exists('event_name', $eventArray)
                    || !\is_string($eventArray['event_name'])
                    || !\array_key_exists('json_payload', $eventArray)
                    || !\is_string($eventArray['json_payload'])
                    || !\array_key_exists('json_metadata', $eventArray)
                    || !\is_string($eventArray['json_metadata'])
                ) {
                    throw new EventStoreCannotRead(new \RuntimeException(\sprintf(
                        'Could not get the correct event properties from the event array for aggregate id %s',
                        $aggregateId
                    )));
                }

                return SerializedEvent::fromProperties(
                    $eventArray['event_id'],
                    $eventArray['aggregate_id'],
                    $eventArray['aggregate_version'],
                    $eventArray['causation_id'],
                    $eventArray['correlation_id'],
                    $eventArray['recorded_on'],
                    $eventArray['event_name'],
                    $eventArray['json_payload'],
                    $eventArray['json_metadata']
                );
            }, $eventArrays);

            $creationEvent = array_shift($aggregateEvents);
            $creationEvent = EventDeserializer::serializedEventsToEvents([$creationEvent])[0];
            $transformationEvents = EventDeserializer::serializedEventsToEvents($aggregateEvents);

            $count = \count($transformationEvents);

            return AggregateAndEventIds::fromProperties(
                AggregateFromEvents::aggregateFromEvents(
                    $creationEvent,
                    $transformationEvents
                ),
                $creationEvent->eventId(),
                $count > 0 ? $transformationEvents[$count - 1]->eventId() : $creationEvent->eventId()
            );
        } catch (\Throwable $exception) {
            throw new EventStoreCannotRead($exception);
        }
    }

    public function findEvent(string $eventId): ?Event
    {
        try {
            if (false === $this->connection->isTransactionActive()) {
                throw new FindingAggregateRequiresActiveTransaction();
            }

            $statement = $this->connection->prepare('SELECT * FROM '.$this->eventStoreTableName.' WHERE event_id = ? FOR UPDATE');
            $statement->bindValue(1, $eventId);

            $eventArray = $statement->executeQuery()->fetchAssociative();
            if (!\is_array($eventArray)) {
                return null;
            }

            if (
                !\array_key_exists('event_id', $eventArray)
                || !\is_string($eventArray['event_id'])
                || !\array_key_exists('aggregate_id', $eventArray)
                || !\is_string($eventArray['aggregate_id'])
                || !\array_key_exists('aggregate_version', $eventArray)
                || !\is_int($eventArray['aggregate_version'])
                || !\array_key_exists('causation_id', $eventArray)
                || !\is_string($eventArray['causation_id'])
                || !\array_key_exists('correlation_id', $eventArray)
                || !\is_string($eventArray['correlation_id'])
                || !\array_key_exists('recorded_on', $eventArray)
                || !\is_string($eventArray['recorded_on'])
                || !\array_key_exists('event_name', $eventArray)
                || !\is_string($eventArray['event_name'])
                || !\array_key_exists('json_payload', $eventArray)
                || !\is_string($eventArray['json_payload'])
                || !\array_key_exists('json_metadata', $eventArray)
                || !\is_string($eventArray['json_metadata'])
            ) {
                throw new EventStoreCannotRead(new \RuntimeException(\sprintf(
                    'Could not get the correct event properties from the event array for event id %s',
                    $eventId
                )));
            }

            $serializedEvent = SerializedEvent::fromProperties(
                $eventArray['event_id'],
                $eventArray['aggregate_id'],
                $eventArray['aggregate_version'],
                $eventArray['causation_id'],
                $eventArray['correlation_id'],
                $eventArray['recorded_on'],
                $eventArray['event_name'],
                $eventArray['json_payload'],
                $eventArray['json_metadata']
            );

            return EventDeserializer::serializedEventsToEvents([$serializedEvent])[0];
        } catch (\Throwable $exception) {
            throw new EventStoreCannotRead($exception);
        }
    }

    public function save(Event $event): void
    {
        try {
            if (false === $this->connection->isTransactionActive()) {
                throw new SavingEventRequiresActiveTransaction();
            }

            $serializedEvent = EventSerializer::eventsToSerializedEvents([$event])[0];

            $this->connection->insert(
                $this->eventStoreTableName,
                [
                    'event_id' => $serializedEvent->eventId(),
                    'aggregate_id' => $serializedEvent->aggregateId(),
                    'aggregate_version' => $serializedEvent->aggregateVersion(),
                    'causation_id' => $serializedEvent->causationId(),
                    'correlation_id' => $serializedEvent->correlationId(),
                    'recorded_on' => $serializedEvent->recordedOn(),
                    'event_name' => $serializedEvent->eventName(),
                    'json_payload' => $serializedEvent->jsonPayload(),
                    'json_metadata' => $serializedEvent->jsonMetadata(),
                ],
                [
                    'event_id' => ParameterType::STRING,
                    'aggregate_id' => ParameterType::STRING,
                    'aggregate_version' => ParameterType::INTEGER,
                    'causation_id' => ParameterType::STRING,
                    'correlation_id' => ParameterType::STRING,
                    'recorded_on' => ParameterType::STRING,
                    'event_name' => ParameterType::STRING,
                    'json_payload' => ParameterType::STRING,
                    'json_metadata' => ParameterType::STRING,
                ]
            );
        } catch (\Throwable $exception) {
            throw new EventStoreCannotWrite($exception);
        }
    }
}

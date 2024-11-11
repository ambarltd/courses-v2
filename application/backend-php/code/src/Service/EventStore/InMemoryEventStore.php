<?php

declare(strict_types=1);

namespace Galeas\Api\Service\EventStore;

use Galeas\Api\Common\Event\AggregateFromEvents;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\Event\EventDeserializer;
use Galeas\Api\Common\Event\EventSerializer;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\Service\EventStore\Exception\CancellingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\CompletingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\FindingAggregateRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\SavingEventRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\TransactionIsAlreadyActive;

/**
 * Used for command handler testing. Emulates the behavior of @see SQLEventStore
 * If the behavior of this event store is similar to the sql version,
 * then this one can be used as a substitute for testing purposes. Not only does
 * this reduce infrastructure concerns while testing, but it also reduces the testing time.
 * For integration or end to end tests, the api endpoints themselves would be tested.
 */
class InMemoryEventStore implements EventStore
{
    /**
     * @var Event[]
     */
    private array $storedEvents = [];

    /**
     * @var Event[]
     */
    private array $uncommittedEvents = [];

    private bool $isTransactionActive = false;

    public function beginTransaction(): void
    {
        try {
            if (true === $this->isTransactionActive) {
                throw new TransactionIsAlreadyActive();
            }

            $this->isTransactionActive = true;

            return;
        } catch (\Throwable $exception) {
            throw new EventStoreCannotWrite($exception);
        }
    }

    public function completeTransaction(): void
    {
        try {
            if (false === $this->isTransactionActive) {
                throw new CompletingTransactionRequiresActiveTransaction();
            }

            $this->isTransactionActive = false;

            $this->storedEvents = array_merge(
                $this->storedEvents,
                $this->uncommittedEvents
            );

            $this->uncommittedEvents = [];

            return;
        } catch (\Throwable $exception) {
            throw new EventStoreCannotWrite($exception);
        }
    }

    public function cancelTransaction(): void
    {
        try {
            if (false === $this->isTransactionActive) {
                throw new CancellingTransactionRequiresActiveTransaction();
            }

            $this->isTransactionActive = false;

            $this->uncommittedEvents = [];

            return;
        } catch (\Throwable $exception) {
            throw new EventStoreCannotWrite($exception);
        }
    }

    public function findAggregateAndEventIdsInLastEvent(string $aggregateId): ?AggregateAndEventIdsInLastEvent
    {
        try {
            if (false === $this->isTransactionActive) {
                throw new FindingAggregateRequiresActiveTransaction();
            }

            $creationEvent = null;
            $transformationEvents = [];
            foreach (array_merge($this->storedEvents, $this->uncommittedEvents) as $event) {
                if (
                    $event->aggregateId()->id() === $aggregateId
                    && null === $creationEvent
                ) {
                    $creationEvent = $event;
                } elseif (
                    $event->aggregateId()->id() === $aggregateId
                    && null !== $creationEvent
                ) {
                    $transformationEvents[] = $event;
                }
            }

            if (null === $creationEvent) {
                return null;
            }

            $countTransformationEvents = \count($transformationEvents);

            return AggregateAndEventIdsInLastEvent::fromProperties(
                AggregateFromEvents::aggregateFromEvents(
                    $creationEvent,
                    $transformationEvents
                ),
                $countTransformationEvents > 0 ? $transformationEvents[$countTransformationEvents - 1]->correlationId() : $creationEvent->correlationId(),
                $countTransformationEvents > 0 ? $transformationEvents[$countTransformationEvents - 1]->eventId() : $creationEvent->eventId()
            );
        } catch (\Throwable $exception) {
            throw new EventStoreCannotRead($exception);
        }
    }

    public function findEvent(string $eventId): ?Event
    {
        foreach ($this->storedEvents as $event) {
            if ($event->eventId()->id() === $eventId) {
                return $event;
            }
        }

        return null;
    }

    public function save(Event $event): void
    {
        if (false === $this->isTransactionActive) {
            throw new EventStoreCannotWrite(new SavingEventRequiresActiveTransaction());
        }

        try {
            $this->uncommittedEvents[] = EventDeserializer::serializedEventsToEvents(
                EventSerializer::eventsToSerializedEvents([$event])
            )[0];
        } catch (\Throwable $exception) {
            throw new EventStoreCannotWrite(new \RuntimeException('Event could not be serialized or deserialized. Have you registered it in the EventReflectionBaseClass?'));
        }
    }

    /**
     * How does this help?
     * It helps you figure out if a command handler saved an event into
     * the event store dependency injected into its constructor.
     *
     * @return Event[]
     */
    public function storedEvents(): array
    {
        return $this->storedEvents;
    }
}

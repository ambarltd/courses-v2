<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Service\EventStore;

use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\Service\EventStore\AggregateAndEventIdsInLastEvent;
use Galeas\Api\Service\EventStore\Exception\CancellingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\CompletingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\FindingAggregateRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\SavingEventRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\InMemoryEventStore;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class InMemoryEventStoreTest extends UnitTest
{
    public function testPersistence(): void
    {
        $inMemoryEventStore = $this->createNewInMemoryEventStore();

        $signedUp = SampleEvents::signedUp();
        $this->saveInTransaction($inMemoryEventStore, [$signedUp]);
        Assert::assertEquals(
            AggregateAndEventIdsInLastEvent::fromProperties(
                $signedUp->createUser(),
                $signedUp->correlationId(),
                $signedUp->eventId()
            ),
            $this->findAggregateAndEventIdsInLastEventInTransaction($inMemoryEventStore, $signedUp->aggregateId()->id())
        );
        Assert::assertEquals(
            [$signedUp],
            $inMemoryEventStore->storedEvents()
        );

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId(),
        );
        $this->saveInTransaction($inMemoryEventStore, [$primaryEmailVerified]);
        Assert::assertEquals(
            AggregateAndEventIdsInLastEvent::fromProperties(
                $primaryEmailVerified->transformUser($signedUp->createUser()),
                $primaryEmailVerified->correlationId(),
                $primaryEmailVerified->eventId()
            ),
            $this->findAggregateAndEventIdsInLastEventInTransaction($inMemoryEventStore, $signedUp->aggregateId()->id())
        );
        Assert::assertEquals(
            [$signedUp, $primaryEmailVerified],
            $inMemoryEventStore->storedEvents()
        );
    }

    public function testAggregateIsAvailableOnPendingTransactionButNotAfterItIsCanceled(): void
    {
        $inMemoryEventStore = $this->createNewInMemoryEventStore();

        $signedUp = SampleEvents::signedUp();
        $this->saveInTransaction($inMemoryEventStore, [$signedUp]);

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId(),
        );
        $inMemoryEventStore->beginTransaction();
        $inMemoryEventStore->save($primaryEmailVerified);
        Assert::assertEquals(
            AggregateAndEventIdsInLastEvent::fromProperties(
                $primaryEmailVerified->transformUser($signedUp->createUser()),
                $primaryEmailVerified->correlationId(),
                $primaryEmailVerified->eventId(),
            ),
            $inMemoryEventStore->findAggregateAndEventIdsInLastEvent($signedUp->aggregateId()->id())
        );
        Assert::assertEquals([$signedUp], $inMemoryEventStore->storedEvents());
        $inMemoryEventStore->cancelTransaction();

        Assert::assertEquals(
            AggregateAndEventIdsInLastEvent::fromProperties(
                $signedUp->createUser(),
                $signedUp->correlationId(),
                $signedUp->eventId(),
            ),
            $this->findAggregateAndEventIdsInLastEventInTransaction($inMemoryEventStore, $signedUp->aggregateId()->id())
        );
        Assert::assertEquals([$signedUp], $inMemoryEventStore->storedEvents());
    }

    public function testCompletingTransactionRequiresActiveTransaction(): void
    {
        try {
            $inMemoryEventStore = $this->createNewInMemoryEventStore();
            $inMemoryEventStore->completeTransaction();
        } catch (EventStoreCannotWrite $eventStoreCannotWrite) {
            Assert::assertInstanceOf(
                CompletingTransactionRequiresActiveTransaction::class,
                $eventStoreCannotWrite->getDatabaseException()
            );

            return;
        }

        Assert::fail('Did not have expected exception');
    }

    public function testCancellingTransactionRequiresActiveTransaction(): void
    {
        try {
            $inMemoryEventStore = $this->createNewInMemoryEventStore();
            $inMemoryEventStore->cancelTransaction();
        } catch (EventStoreCannotWrite $eventStoreCannotWrite) {
            Assert::assertInstanceOf(
                CancellingTransactionRequiresActiveTransaction::class,
                $eventStoreCannotWrite->getDatabaseException()
            );

            return;
        }

        Assert::fail('Did not have expected exception');
    }

    public function testFindingAggregateRequiresActiveTransaction(): void
    {
        try {
            $inMemoryEventStore = $this->createNewInMemoryEventStore();
            $inMemoryEventStore->findAggregateAndEventIdsInLastEvent('made_up_id');
        } catch (EventStoreCannotRead $eventStoreCannotWrite) {
            Assert::assertInstanceOf(
                FindingAggregateRequiresActiveTransaction::class,
                $eventStoreCannotWrite->getDatabaseException()
            );

            return;
        }

        Assert::fail('Did not have expected exception');
    }

    public function testSavingEventRequiresActiveTransaction(): void
    {
        try {
            $inMemoryEventStore = $this->createNewInMemoryEventStore();

            $signedUp = SampleEvents::signedUp();
            $inMemoryEventStore->save($signedUp);
        } catch (EventStoreCannotWrite $eventStoreCannotWrite) {
            Assert::assertInstanceOf(
                SavingEventRequiresActiveTransaction::class,
                $eventStoreCannotWrite->getDatabaseException()
            );

            return;
        }

        Assert::fail('Did not have expected exception');
    }

    private function createNewInMemoryEventStore(): InMemoryEventStore
    {
        return new InMemoryEventStore();
    }

    private function saveInTransaction(InMemoryEventStore $inMemoryEventStore, array $events): void
    {
        $inMemoryEventStore->beginTransaction();
        foreach ($events as $event) {
            $inMemoryEventStore->save($event);
        }
        $inMemoryEventStore->completeTransaction();
    }

    private function findAggregateAndEventIdsInLastEventInTransaction(InMemoryEventStore $inMemoryEventStore, string $aggregateId): ?AggregateAndEventIdsInLastEvent
    {
        $inMemoryEventStore->beginTransaction();
        $aggregateAndEventIdsInLastEvent = $inMemoryEventStore->findAggregateAndEventIdsInLastEvent($aggregateId);
        $inMemoryEventStore->completeTransaction();

        return $aggregateAndEventIdsInLastEvent;
    }
}

<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Service\EventStore;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\Service\EventStore\AggregateAndEventIdsInLastEvent;
use Galeas\Api\Service\EventStore\Exception\CancellingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\CompletingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\FindingAggregateRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\SavingEventRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\InMemoryEventStore;
use Galeas\Api\Service\EventStore\SQLEventStore;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SQLEventStoreTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testPersistence(): void
    {
        $sqlEventStore = $this->getSQLEventStore();

        $signedUp = SampleEvents::signedUp();
        $this->saveInTransaction($sqlEventStore, [$signedUp]);
        Assert::assertEquals(
            AggregateAndEventIdsInLastEvent::fromProperties(
                $signedUp->createUser(),
                $signedUp->correlationId(),
                $signedUp->eventId()
            ),
            $this->findAggregateAndEventIdsInLastEventInTransaction($sqlEventStore, $signedUp->aggregateId()->id())
        );

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId(),
        );
        $this->saveInTransaction($sqlEventStore, [$primaryEmailVerified]);
        Assert::assertEquals(
            AggregateAndEventIdsInLastEvent::fromProperties(
                $primaryEmailVerified->transformUser($signedUp->createUser()),
                $primaryEmailVerified->correlationId(),
                $primaryEmailVerified->eventId()
            ),
            $this->findAggregateAndEventIdsInLastEventInTransaction($sqlEventStore, $signedUp->aggregateId()->id())
        );
    }

    public function testAggregateIsAvailableOnPendingTransactionButNotAfterItIsCanceled(): void
    {
        $sqlEventStore = $this->getSQLEventStore();

        $signedUp = SampleEvents::signedUp();
        $this->saveInTransaction($sqlEventStore, [$signedUp]);

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId(),
        );
        $sqlEventStore->beginTransaction();
        $sqlEventStore->save($primaryEmailVerified);
        Assert::assertEquals(
            AggregateAndEventIdsInLastEvent::fromProperties(
                $primaryEmailVerified->transformUser($signedUp->createUser()),
                $primaryEmailVerified->correlationId(),
                $primaryEmailVerified->eventId(),
            ),
            $sqlEventStore->findAggregateAndEventIdsInLastEvent($signedUp->aggregateId()->id())
        );
        $sqlEventStore->cancelTransaction();

        Assert::assertEquals(
            AggregateAndEventIdsInLastEvent::fromProperties(
                $signedUp->createUser(),
                $signedUp->correlationId(),
                $signedUp->eventId(),
            ),
            $this->findAggregateAndEventIdsInLastEventInTransaction($sqlEventStore, $signedUp->aggregateId()->id())
        );
    }

    public function testCompletingTransactionRequiresActiveTransaction(): void
    {
        try {
            $sqlEventStore = $this->getSQLEventStore();
            $sqlEventStore->completeTransaction();
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
            $sqlEventStore = $this->getSQLEventStore();
            $sqlEventStore->cancelTransaction();
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
            $sqlEventStore = $this->getSQLEventStore();
            $sqlEventStore->findAggregateAndEventIdsInLastEvent('made_up_id');
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
            $sqlEventStore = $this->getSQLEventStore();

            $signedUp = SampleEvents::signedUp();
            $sqlEventStore->save($signedUp);
        } catch (EventStoreCannotWrite $eventStoreCannotWrite) {
            Assert::assertInstanceOf(
                SavingEventRequiresActiveTransaction::class,
                $eventStoreCannotWrite->getDatabaseException()
            );

            return;
        }

        Assert::fail('Did not have expected exception');
    }

    private function saveInTransaction(SQLEventStore $sqlEventStore, array $events): void
    {
        $sqlEventStore->beginTransaction();
        foreach ($events as $event) {
            $sqlEventStore->save($event);
        }
        $sqlEventStore->completeTransaction();
    }

    private function findAggregateAndEventIdsInLastEventInTransaction(SQLEventStore $sqlEventStore, string $aggregateId): ?AggregateAndEventIdsInLastEvent
    {
        $sqlEventStore->beginTransaction();
        $aggregateAndEventIdsInLastEvent = $sqlEventStore->findAggregateAndEventIdsInLastEvent($aggregateId);
        $sqlEventStore->completeTransaction();

        return $aggregateAndEventIdsInLastEvent;
    }
}

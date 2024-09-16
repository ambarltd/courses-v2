<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Service\EventStore;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Service\EventStore\AggregateAndEventIds;
use Galeas\Api\Service\EventStore\Exception\CancellingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\CompletingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\FindingAggregateRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\SavingEventRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\InMemoryEventStore;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class InMemoryEventStoreTest extends UnitTestBase
{
    private function createNewInMemoryEventStore(): InMemoryEventStore
    {
        return new InMemoryEventStore();
    }

    public function testPersistence(): void
    {
        $inMemoryEventStore = $this->createNewInMemoryEventStore();

        $signedUp = SampleEvents::signedUp();
        $inMemoryEventStore->beginTransaction();
        $inMemoryEventStore->save($signedUp);
        $inMemoryEventStore->completeTransaction();

        $inMemoryEventStore->beginTransaction();
        $aggregateAndEventIds = $inMemoryEventStore->find($signedUp->aggregateId()->id());
        $inMemoryEventStore->completeTransaction();

        if (!($aggregateAndEventIds instanceof AggregateAndEventIds)) {
            throw new \Exception();
        }
        $user = $aggregateAndEventIds->aggregate();
        if (!($user instanceof User)) {
            throw new \Exception();
        }

        if ($user->aggregateId() !== $signedUp->aggregateId()) {
            throw new \Exception();
        }

        if (!($user->primaryEmailStatus() instanceof UnverifiedEmail)) {
            throw new \Exception();
        }

        if ($user->primaryEmailStatus()->email()->email() !== $signedUp->primaryEmail()) {
            throw new \Exception();
        }

        Assert::assertCount(1, $inMemoryEventStore->storedEvents());
        Assert::assertEquals(
            $signedUp,
            $inMemoryEventStore->storedEvents()[0]
        );
    }

    public function testAggregateIsNotSavedUntilTransactionIsCompleted(): void
    {
        $inMemoryEventStore = $this->createNewInMemoryEventStore();

        $signedUp = SampleEvents::signedUp();
        $inMemoryEventStore->beginTransaction();
        $inMemoryEventStore->save($signedUp);
        $inMemoryEventStore->cancelTransaction();

        $inMemoryEventStore->beginTransaction();
        $user = $inMemoryEventStore->find($signedUp->aggregateId()->id());

        Assert::assertEquals(
            null,
            $user
        );
        Assert::assertCount(0, $inMemoryEventStore->storedEvents());
        $inMemoryEventStore->completeTransaction();
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
            $inMemoryEventStore->find('made_up_id');
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
}

<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Service\EventStore;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Service\EventStore\Exception\CancellingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\CompletingTransactionRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\FindingAggregateRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\Exception\SavingEventRequiresActiveTransaction;
use Galeas\Api\Service\EventStore\InMemoryEventStore;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class InMemoryEventStoreTest extends UnitTestBase
{
    private function createNewInMemoryEventStore(): InMemoryEventStore
    {
        return new InMemoryEventStore();
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testPersistence(): void
    {
        $inMemoryEventStore = $this->createNewInMemoryEventStore();

        $signedUp = SignedUp::fromProperties(
            [],
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $inMemoryEventStore->beginTransaction();
        $inMemoryEventStore->save($signedUp);
        $inMemoryEventStore->completeTransaction();

        $inMemoryEventStore->beginTransaction();

        $user = $inMemoryEventStore->find($signedUp->aggregateId()->id());
        $inMemoryEventStore->completeTransaction();

        if (!($user instanceof User)) {
            throw new \Exception();
        }

        if (!($user->primaryEmailStatus() instanceof UnverifiedEmail)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            ValidEmails::listValidEmails()[0],
            $user->primaryEmailStatus()->email()->email()
        );
        Assert::assertInternalType(
            'string',
            $user->primaryEmailStatus()->verificationCode()->verificationCode()
        );
        Assert::assertTrue(
            password_verify(
                ValidPasswords::listValidPasswords()[0],
                $user->hashedPassword()->hash()
            )
        );
        Assert::assertEquals(
            ValidUsernames::listValidUsernames()[0],
            $user->accountDetails()->username()
        );
        Assert::assertEquals(
            true,
            $user->accountDetails()->termsOfUseAccepted()
        );

        $storedEvent = $inMemoryEventStore->storedEvents()[0];

        if (!($storedEvent instanceof SignedUp)) {
            throw new \Exception();
        }
        Assert::assertEquals(
            ValidEmails::listValidEmails()[0],
            $storedEvent->primaryEmail()
        );
        Assert::assertInternalType(
            'string',
            $storedEvent->primaryEmailVerificationCode()
        );
        Assert::assertTrue(
            password_verify(
                ValidPasswords::listValidPasswords()[0],
                $storedEvent->hashedPassword()
            )
        );
        Assert::assertEquals(
            ValidUsernames::listValidUsernames()[0],
            $storedEvent->username()
        );
        Assert::assertEquals(
            true,
            $storedEvent->termsOfUseAccepted()
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testAggregateIsNotSavedUntilTransactionIsCompleted(): void
    {
        $inMemoryEventStore = $this->createNewInMemoryEventStore();

        $signedUp = SignedUp::fromProperties(
            [],
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $inMemoryEventStore->beginTransaction();
        $inMemoryEventStore->save($signedUp);
        $inMemoryEventStore->cancelTransaction();

        $inMemoryEventStore->beginTransaction();
        $user = $inMemoryEventStore->find($signedUp->aggregateId()->id());

        Assert::assertEquals(
            null,
            $user
        );
        $inMemoryEventStore->completeTransaction();
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function testSavingEventRequiresActiveTransaction(): void
    {
        try {
            $inMemoryEventStore = $this->createNewInMemoryEventStore();

            $signedUp = SignedUp::fromProperties(
                [],
                ValidEmails::listValidEmails()[0],
                ValidPasswords::listValidPasswords()[0],
                ValidUsernames::listValidUsernames()[0],
                true
            );
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

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
use Galeas\Api\Service\EventStore\SQLEventStore;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;

class SQLEventStoreTest extends KernelTestBase
{
    /**
     * @test
     *
     * @throws \Exception
     */
    public function testDatabasePersistence(): void
    {
        $sqlEventStore = $this->getContainer()->get(SQLEventStore::class);

        $signedUp = SignedUp::fromProperties(
            [],
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $sqlEventStore->beginTransaction();
        $sqlEventStore->save($signedUp);
        $sqlEventStore->completeTransaction();

        $sqlEventStore->beginTransaction();
        $user = $sqlEventStore->find($signedUp->aggregateId()->id());
        $sqlEventStore->completeTransaction();

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
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testAggregateIsNotSavedUntilTransactionIsCompleted(): void
    {
        $sqlEventStore = $this->getContainer()->get(SQLEventStore::class);

        $signedUp = SignedUp::fromProperties(
            [],
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $sqlEventStore->beginTransaction();
        $sqlEventStore->save($signedUp);
        $sqlEventStore->cancelTransaction();

        $sqlEventStore->beginTransaction();
        $user = $sqlEventStore->find($signedUp->aggregateId()->id());

        Assert::assertEquals(
            null,
            $user
        );
        $sqlEventStore->completeTransaction();
    }

    /**
     * @test
     */
    public function testCompletingTransactionRequiresActiveTransaction(): void
    {
        try {
            $sqlEventStore = $this->getContainer()->get(SQLEventStore::class);
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

    /**
     * @test
     */
    public function testCancellingTransactionRequiresActiveTransaction(): void
    {
        try {
            $sqlEventStore = $this->getContainer()->get(SQLEventStore::class);
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

    /**
     * @test
     */
    public function testFindingAggregateRequiresActiveTransaction(): void
    {
        try {
            $sqlEventStore = $this->getContainer()->get(SQLEventStore::class);
            $sqlEventStore->find('made_up_id');
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
            $sqlEventStore = $this->getContainer()->get(SQLEventStore::class);

            $signedUp = SignedUp::fromProperties(
                [],
                ValidEmails::listValidEmails()[0],
                ValidPasswords::listValidPasswords()[0],
                ValidUsernames::listValidUsernames()[0],
                true
            );
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
}

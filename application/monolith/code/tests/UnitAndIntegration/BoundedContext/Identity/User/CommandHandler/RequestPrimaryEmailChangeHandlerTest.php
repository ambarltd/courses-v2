<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\CommandHandler;

use Galeas\Api\BoundedContext\Identity\User\Command\RequestPrimaryEmailChange;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\IsEmailTaken;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\RequestPrimaryEmailChangeHandler;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\InvalidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Id\ValidIds;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;

class RequestPrimaryEmailChangeHandlerTest extends HandlerTestBase
{
    /**
     * @test
     *
     * @throws \Exception
     */
    public function testHandleForVerifiedEmail(): void
    {
        $signedUp = SignedUp::fromProperties(
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $primaryEmailVerified = PrimaryEmailVerified::fromProperties(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            $this->mockMetadata(),
            $signedUp->primaryEmailVerificationCode()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithCallback(
                IsEmailTaken::class,
                'isEmailTaken',
                function (string $email): bool {
                    if ($email === ValidEmails::listValidEmails()[1]) {
                        return false;
                    }

                    return true;
                }
            )
        );

        $command = new RequestPrimaryEmailChange();
        $command->authorizerId = $signedUp->aggregateId()->id();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = ValidEmails::listValidEmails()[1];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[2];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        if (!($storedEvent instanceof PrimaryEmailChangeRequested)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            $storedEvent,
            $queuedEvent
        );
        Assert::assertEquals(
            $command->newEmailRequested,
            $storedEvent->newEmailRequested()
        );
        Assert::assertEquals(
            $command->authorizerId,
            $storedEvent->authorizerId()->id()
        );
        Assert::assertEquals(
            $command->authorizerId,
            $storedEvent->aggregateId()->id()
        );
        Assert::assertInternalType(
            'string',
            $storedEvent->newVerificationCode()
        );
        Assert::assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
    }

    /**
     * @test
     */
    public function testHandleForRequestedEmail(): void
    {
        $signedUp = SignedUp::fromProperties(
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $primaryEmailVerified = PrimaryEmailVerified::fromProperties(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            $this->mockMetadata(),
            $signedUp->primaryEmailVerificationCode()
        );
        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::fromProperties(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[1],
            $signedUp->hashedPassword()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->save($primaryEmailChangeRequested);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithCallback(
                IsEmailTaken::class,
                'isEmailTaken',
                function (string $email): bool {
                    if ($email === ValidEmails::listValidEmails()[2]) {
                        return false;
                    }

                    return true;
                }
            )
        );

        $command = new RequestPrimaryEmailChange();
        $command->authorizerId = $signedUp->aggregateId()->id();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = ValidEmails::listValidEmails()[2];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[3];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        if (!($storedEvent instanceof PrimaryEmailChangeRequested)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            $storedEvent,
            $queuedEvent
        );
        Assert::assertEquals(
            $command->newEmailRequested,
            $storedEvent->newEmailRequested()
        );
        Assert::assertEquals(
            $command->authorizerId,
            $storedEvent->authorizerId()->id()
        );
        Assert::assertEquals(
            $command->authorizerId,
            $storedEvent->aggregateId()->id()
        );
        Assert::assertInternalType(
            'string',
            $storedEvent->newVerificationCode()
        );
        Assert::assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\UserNotFound
     */
    public function testUserNotFound(): void
    {
        $signedUp = SignedUp::fromProperties(
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $primaryEmailVerified = PrimaryEmailVerified::fromProperties(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            $this->mockMetadata(),
            $signedUp->primaryEmailVerificationCode()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            )
        );

        $command = new RequestPrimaryEmailChange();
        $command->authorizerId = ValidIds::listValidIds()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = ValidEmails::listValidEmails()[1];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\EmailIsNotChanging
     */
    public function testEmailIsNotChangingForUnverifiedEmail(): void
    {
        $signedUp = SignedUp::fromProperties(
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );

        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            )
        );

        $command = new RequestPrimaryEmailChange();
        $command->authorizerId = $signedUp->aggregateId()->id();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = $signedUp->primaryEmail();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\EmailIsNotChanging
     */
    public function testEmailIsNotChangingForVerifiedEmail(): void
    {
        $signedUp = SignedUp::fromProperties(
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $primaryEmailVerified = PrimaryEmailVerified::fromProperties(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            $this->mockMetadata(),
            $signedUp->primaryEmailVerificationCode()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            )
        );

        $command = new RequestPrimaryEmailChange();
        $command->authorizerId = $signedUp->aggregateId()->id();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = $signedUp->primaryEmail();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\EmailIsNotChanging
     */
    public function testEmailIsNotChangingForRequestedEmailWithPreviouslyVerifiedEmail(): void
    {
        $signedUp = SignedUp::fromProperties(
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $primaryEmailVerified = PrimaryEmailVerified::fromProperties(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            $this->mockMetadata(),
            $signedUp->primaryEmailVerificationCode()
        );
        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::fromProperties(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[1],
            $signedUp->hashedPassword()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->save($primaryEmailChangeRequested);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            )
        );

        $command = new RequestPrimaryEmailChange();
        $command->authorizerId = $signedUp->aggregateId()->id();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = $signedUp->primaryEmail();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\EmailIsNotChanging
     */
    public function testEmailIsNotChangingForRequestedEmailWithPreviouslyRequestedEmail(): void
    {
        $signedUp = SignedUp::fromProperties(
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $primaryEmailVerified = PrimaryEmailVerified::fromProperties(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            $this->mockMetadata(),
            $signedUp->primaryEmailVerificationCode()
        );
        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::fromProperties(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[1],
            $signedUp->hashedPassword()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->save($primaryEmailChangeRequested);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            )
        );

        $command = new RequestPrimaryEmailChange();
        $command->authorizerId = $signedUp->aggregateId()->id();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = $primaryEmailChangeRequested->newEmailRequested();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\PasswordDoesNotMatch
     */
    public function testPasswordDoesNotMatch(): void
    {
        $signedUp = SignedUp::fromProperties(
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $primaryEmailVerified = PrimaryEmailVerified::fromProperties(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            $this->mockMetadata(),
            $signedUp->primaryEmailVerificationCode()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            )
        );

        $command = new RequestPrimaryEmailChange();
        $command->authorizerId = $signedUp->aggregateId()->id();
        $command->password = ValidPasswords::listValidPasswords()[1];
        $command->newEmailRequested = ValidEmails::listValidEmails()[1];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\InvalidEmail
     */
    public function testInvalidEmail(): void
    {
        $signedUp = SignedUp::fromProperties(
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $primaryEmailVerified = PrimaryEmailVerified::fromProperties(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            $this->mockMetadata(),
            $signedUp->primaryEmailVerificationCode()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            )
        );

        $command = new RequestPrimaryEmailChange();
        $command->authorizerId = $signedUp->aggregateId()->id();
        $command->authorizerId = $signedUp->aggregateId()->id();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = InvalidEmails::listInvalidEmails()[0];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\EmailIsTaken
     */
    public function testEmailIsTaken(): void
    {
        $signedUp = SignedUp::fromProperties(
            $this->mockMetadata(),
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $primaryEmailVerified = PrimaryEmailVerified::fromProperties(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            $this->mockMetadata(),
            $signedUp->primaryEmailVerificationCode()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                true
            )
        );

        $command = new RequestPrimaryEmailChange();
        $command->authorizerId = $signedUp->aggregateId()->id();
        $command->authorizerId = $signedUp->aggregateId()->id();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = ValidEmails::listValidEmails()[1];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}

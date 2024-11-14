<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\CommandHandler;

use Galeas\Api\BoundedContext\Identity\User\Command\RequestPrimaryEmailChange;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\EmailIsNotChanging;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\InvalidEmail;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\PasswordDoesNotMatch;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\RequestPrimaryEmailChangeHandler;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\UnverifiedUserCannotRequestPrimaryEmailChange;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\UserNotFound;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerUnitTest;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\InvalidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Id\ValidIds;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class RequestPrimaryEmailChangeHandlerUnitTest extends HandlerUnitTest
{
    public function testHandleForVerifiedEmail(): void
    {
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore()
        );

        $command = new RequestPrimaryEmailChange();
        $command->authenticatedUserId = $signedUp->aggregateId()->id();
        $command->password = 'abcDEFg1/2'; // known password for the hash in signedUp
        $command->newEmailRequested = 'new_email_requested@example.com';
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $primaryEmailChangeRequested = $this->getInMemoryEventStore()->storedEvents()[2];

        if (!$primaryEmailChangeRequested instanceof PrimaryEmailChangeRequested) {
            throw new \Exception();
        }

        Assert::assertEquals(
            [
                $primaryEmailChangeRequested->eventId(),
                $signedUp->aggregateId(),
                $primaryEmailVerified->aggregateVersion() + 1,
                $primaryEmailVerified->eventId(),
                $signedUp->eventId(),
                $primaryEmailChangeRequested->recordedOn(),
                $command->metadata,
                $command->newEmailRequested,
                $primaryEmailChangeRequested->newVerificationCode(),
                $signedUp->hashedPassword(),
            ],
            [
                $primaryEmailChangeRequested->eventId(),
                $primaryEmailChangeRequested->aggregateId(),
                $primaryEmailChangeRequested->aggregateVersion(),
                $primaryEmailChangeRequested->causationId(),
                $primaryEmailChangeRequested->correlationId(),
                $primaryEmailChangeRequested->recordedOn(),
                $primaryEmailChangeRequested->metadata(),
                $primaryEmailChangeRequested->newEmailRequested(),
                $primaryEmailChangeRequested->newVerificationCode(),
                $primaryEmailChangeRequested->requestedWithHashedPassword(),
            ]
        );
    }

    public function testHandleForRequestedEmail(): void
    {
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $primaryEmailChangeRequested = SampleEvents::primaryEmailChangeRequested(
            $signedUp->aggregateId(),
            3,
            $primaryEmailVerified->eventId(),
            $signedUp->eventId()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->save($primaryEmailChangeRequested);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore()
        );

        $command = new RequestPrimaryEmailChange();
        $command->authenticatedUserId = $signedUp->aggregateId()->id();
        $command->password = 'abcDEFg1/2'; // known password for the hash in signedUp
        $command->newEmailRequested = 'new_email_requested@example.com';
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[3];

        if (!$storedEvent instanceof PrimaryEmailChangeRequested) {
            throw new \Exception();
        }

        Assert::assertEquals(
            [
                $storedEvent->eventId(),
                $signedUp->aggregateId(),
                $primaryEmailChangeRequested->aggregateVersion() + 1,
                $primaryEmailChangeRequested->eventId(),
                $signedUp->eventId(),
                $storedEvent->recordedOn(),
                $command->metadata,
                $command->newEmailRequested,
                $storedEvent->newVerificationCode(),
                $signedUp->hashedPassword(),
            ],
            [
                $storedEvent->eventId(),
                $storedEvent->aggregateId(),
                $storedEvent->aggregateVersion(),
                $storedEvent->causationId(),
                $storedEvent->correlationId(),
                $storedEvent->recordedOn(),
                $storedEvent->metadata(),
                $storedEvent->newEmailRequested(),
                $storedEvent->newVerificationCode(),
                $storedEvent->requestedWithHashedPassword(),
            ]
        );
    }

    public function testUserNotFound(): void
    {
        $this->expectException(UserNotFound::class);
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore()
        );

        $command = new RequestPrimaryEmailChange();
        $command->authenticatedUserId = ValidIds::listValidIds()[0];
        $command->password = 'abcDEFg1/2'; // known password for the hash in signedUp
        $command->newEmailRequested = ValidEmails::listValidEmails()[1];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testUnverifiedUserCannotRequestPrimaryEmailChange(): void
    {
        $this->expectException(UnverifiedUserCannotRequestPrimaryEmailChange::class);
        $signedUp = SampleEvents::signedUp();

        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore()
        );

        $command = new RequestPrimaryEmailChange();
        $command->authenticatedUserId = $signedUp->aggregateId()->id();
        $command->password = 'abcDEFg1/2'; // known password for the hash in signedUp
        $command->newEmailRequested = $signedUp->primaryEmail();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testEmailIsNotChangingForVerifiedEmail(): void
    {
        $this->expectException(EmailIsNotChanging::class);
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore()
        );

        $command = new RequestPrimaryEmailChange();
        $command->authenticatedUserId = $signedUp->aggregateId()->id();
        $command->password = 'abcDEFg1/2'; // known password for the hash in signedUp
        $command->newEmailRequested = $signedUp->primaryEmail();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testEmailIsNotChangingForRequestedEmailWithPreviouslyVerifiedEmail(): void
    {
        $this->expectException(EmailIsNotChanging::class);
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $primaryEmailChangeRequested = SampleEvents::primaryEmailChangeRequested(
            $signedUp->aggregateId(),
            3,
            $primaryEmailVerified->eventId(),
            $signedUp->eventId()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->save($primaryEmailChangeRequested);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore()
        );

        $command = new RequestPrimaryEmailChange();
        $command->authenticatedUserId = $signedUp->aggregateId()->id();
        $command->password = 'abcDEFg1/2'; // known password for the hash in signedUp
        $command->newEmailRequested = $signedUp->primaryEmail();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testEmailIsNotChangingForRequestedEmailWithPreviouslyRequestedEmail(): void
    {
        $this->expectException(EmailIsNotChanging::class);
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $primaryEmailChangeRequested = SampleEvents::primaryEmailChangeRequested(
            $signedUp->aggregateId(),
            3,
            $primaryEmailVerified->eventId(),
            $signedUp->eventId()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->save($primaryEmailChangeRequested);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore()
        );

        $command = new RequestPrimaryEmailChange();
        $command->authenticatedUserId = $signedUp->aggregateId()->id();
        $command->password = 'abcDEFg1/2'; // known password for the hash in signedUp
        $command->newEmailRequested = $primaryEmailChangeRequested->newEmailRequested();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testPasswordDoesNotMatch(): void
    {
        $this->expectException(PasswordDoesNotMatch::class);
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore(),
        );

        $command = new RequestPrimaryEmailChange();
        $command->authenticatedUserId = $signedUp->aggregateId()->id();
        $command->password = 'somePasswordThatDoesNotMatch12391283'; // known password = "abcDEFg1/2" for the hash in signedUp
        $command->newEmailRequested = 'new_email_requested@example.com';
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testInvalidEmail(): void
    {
        $this->expectException(InvalidEmail::class);
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore()
        );

        $command = new RequestPrimaryEmailChange();
        $command->authenticatedUserId = $signedUp->aggregateId()->id();
        $command->authenticatedUserId = $signedUp->aggregateId()->id();
        $command->password = 'abcDEFg1/2'; // known password for the hash in signedUp
        $command->newEmailRequested = InvalidEmails::listInvalidEmails()[0];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}

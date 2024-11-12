<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\CommandHandler;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\AbandonedEmailRetaken;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\EmailAbandoned;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\EmailTaken;
use Galeas\Api\BoundedContext\Identity\User\Command\RequestPrimaryEmailChange;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\EmailIsNotChanging;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\EmailIsTaken;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\InvalidEmail;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\PasswordDoesNotMatch;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\RequestPrimaryEmailChangeHandler;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\UserNotFound;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerUnitTest;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\InvalidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Id\ValidIds;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class RequestPrimaryEmailChangeHandlerUnitTest extends HandlerUnitTest
{
    public function testHandleForUnverifiedEmail(): void
    {
        $signedUp = SampleEvents::signedUp();
        $originalEmailTaken = SampleEvents::emailTaken($signedUp->primaryEmail(), $signedUp->aggregateId());
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($originalEmailTaken);
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
                $signedUp->aggregateVersion() + 1,
                $signedUp->eventId(),
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

        /** @var EmailTaken $emailTaken */
        $emailTaken = $this->getInMemoryEventStore()->storedEvents()[3];

        Assert::assertEquals(
            [
                $emailTaken->eventId(),
                Id::createNewByHashing(
                    'Identity_TakenEmail:'.strtolower($command->newEmailRequested)
                ),
                1,
                $emailTaken->eventId(),
                $emailTaken->eventId(),
                $emailTaken->recordedOn(),
                $command->metadata,
                strtolower($command->newEmailRequested),
                $signedUp->aggregateId(),
            ],
            [
                $emailTaken->eventId(),
                $emailTaken->aggregateId(),
                $emailTaken->aggregateVersion(),
                $emailTaken->causationId(),
                $emailTaken->correlationId(),
                $emailTaken->recordedOn(),
                $emailTaken->metadata(),
                $emailTaken->takenEmailInLowercase(),
                $emailTaken->takenByUser(),
            ]
        );

        /** @var EmailAbandoned $emailAbandoned */
        $emailAbandoned = $this->getInMemoryEventStore()->storedEvents()[4];

        Assert::assertEquals(
            [
                $emailAbandoned->eventId(),
                $originalEmailTaken->aggregateId(),
                $originalEmailTaken->aggregateVersion() + 1,
                $originalEmailTaken->eventId(),
                $originalEmailTaken->eventId(),
                $emailAbandoned->recordedOn(),
                $command->metadata,
            ],
            [
                $emailAbandoned->eventId(),
                $emailAbandoned->aggregateId(),
                $emailAbandoned->aggregateVersion(),
                $emailAbandoned->causationId(),
                $emailAbandoned->correlationId(),
                $emailAbandoned->recordedOn(),
                $emailAbandoned->metadata(),
            ]
        );
    }
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

        /** @var EmailTaken $emailTaken */
        $emailTaken = $this->getInMemoryEventStore()->storedEvents()[3];

        Assert::assertEquals(
            [
                $emailTaken->eventId(),
                Id::createNewByHashing(
                    'Identity_TakenEmail:'.strtolower($command->newEmailRequested)
                ),
                1,
                $emailTaken->eventId(),
                $emailTaken->eventId(),
                $emailTaken->recordedOn(),
                $command->metadata,
                strtolower($command->newEmailRequested),
                $signedUp->aggregateId(),
            ],
            [
                $emailTaken->eventId(),
                $emailTaken->aggregateId(),
                $emailTaken->aggregateVersion(),
                $emailTaken->causationId(),
                $emailTaken->correlationId(),
                $emailTaken->recordedOn(),
                $emailTaken->metadata(),
                $emailTaken->takenEmailInLowercase(),
                $emailTaken->takenByUser(),
            ]
        );
    }

    public function testHandleAbandonedTakenEmailForVerifiedEmail(): void
    {
        $emailTaken = SampleEvents::emailTaken(
            'new_email_requested@example.com',
            Id::createNew(),
        );
        $emailAbandoned = SampleEvents::emailAbandoned(
            $emailTaken->aggregateId(),
            2,
            $emailTaken->eventId(),
            $emailTaken->eventId(),
        );
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($emailTaken);
        $eventStore->save($emailAbandoned);
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

        $primaryEmailChangeRequested = $this->getInMemoryEventStore()->storedEvents()[4];

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

        /** @var AbandonedEmailRetaken $abandonedEmailRetaken */
        $abandonedEmailRetaken = $this->getInMemoryEventStore()->storedEvents()[5];

        Assert::assertEquals(
            [
                $abandonedEmailRetaken->eventId(),
                Id::createNewByHashing(
                    'Identity_TakenEmail:'.strtolower($command->newEmailRequested)
                ),
                3,
                $emailAbandoned->eventId(),
                $emailTaken->eventId(),
                $abandonedEmailRetaken->recordedOn(),
                $command->metadata,
                $signedUp->aggregateId(),
            ],
            [
                $abandonedEmailRetaken->eventId(),
                $abandonedEmailRetaken->aggregateId(),
                $abandonedEmailRetaken->aggregateVersion(),
                $abandonedEmailRetaken->causationId(),
                $abandonedEmailRetaken->correlationId(),
                $abandonedEmailRetaken->recordedOn(),
                $abandonedEmailRetaken->metadata(),
                $abandonedEmailRetaken->retakenByUser(),
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
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = ValidEmails::listValidEmails()[1];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testEmailIsNotChangingForUnverifiedEmail(): void
    {
        $this->expectException(EmailIsNotChanging::class);
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
        $command->password = ValidPasswords::listValidPasswords()[0];
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
        $command->password = ValidPasswords::listValidPasswords()[0];
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
        $command->password = ValidPasswords::listValidPasswords()[0];
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
        $command->password = ValidPasswords::listValidPasswords()[0];
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
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = InvalidEmails::listInvalidEmails()[0];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testEmailIsTaken(): void
    {
        $this->expectException(EmailIsTaken::class);
        $emailTaken = SampleEvents::emailTaken(
            ValidEmails::listValidEmails()[1],
            Id::createNew(),
        );
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($emailTaken);
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore()
        );

        $command = new RequestPrimaryEmailChange();
        $command->authenticatedUserId = $signedUp->aggregateId()->id();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = ValidEmails::listValidEmails()[1];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testEmailIsRetaken(): void
    {
        $this->expectException(EmailIsTaken::class);
        $emailTaken = SampleEvents::emailTaken(
            ValidEmails::listValidEmails()[1],
            Id::createNew(),
        );
        $emailAbandoned = SampleEvents::emailAbandoned(
            $emailTaken->aggregateId(),
            2,
            $emailTaken->eventId(),
            $emailTaken->eventId(),
        );
        $abandonedEmailRetaken = SampleEvents::abandonedEmailRetaken(
            $emailTaken->aggregateId(),
            3,
            $emailAbandoned->eventId(),
            $emailTaken->eventId(),
            Id::createNew()
        );
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($emailTaken);
        $eventStore->save($emailAbandoned);
        $eventStore->save($abandonedEmailRetaken);
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->completeTransaction();

        $handler = new RequestPrimaryEmailChangeHandler(
            $this->getInMemoryEventStore()
        );

        $command = new RequestPrimaryEmailChange();
        $command->authenticatedUserId = $signedUp->aggregateId()->id();
        $command->authenticatedUserId = $signedUp->aggregateId()->id();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->newEmailRequested = ValidEmails::listValidEmails()[1];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}

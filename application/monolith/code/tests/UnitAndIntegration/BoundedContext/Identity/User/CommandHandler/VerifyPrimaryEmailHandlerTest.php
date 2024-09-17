<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\CommandHandler;

use Galeas\Api\BoundedContext\Identity\User\Command\VerifyPrimaryEmail;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\EmailIsAlreadyVerified;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\NoUserFoundForCode;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\UserIdFromPrimaryEmailVerificationCode;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\VerificationCodeDoesNotMatch;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\VerifyPrimaryEmailHandler;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveCreation\Email\EmailVerificationCodeCreator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidVerificationCodes;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class VerifyPrimaryEmailHandlerTest extends HandlerTestBase
{
    public function testHandleUnverifiedEmail(): void
    {
        $signedUp = SampleEvents::signedUp();
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->completeTransaction();

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithCallback(
                UserIdFromPrimaryEmailVerificationCode::class,
                'userIdFromPrimaryEmailVerificationCode',
                function (string $primaryEmailVerificationCode) use ($signedUp): ?string {
                    if ($signedUp->primaryEmailVerificationCode() === $primaryEmailVerificationCode) {
                        return $signedUp->aggregateId()->id();
                    }

                    return null;
                }
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = $signedUp->primaryEmailVerificationCode();
        $command->metadata = $signedUp->metadata();

        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[1];

        if (!$storedEvent instanceof PrimaryEmailVerified) {
            throw new \Exception();
        }

        Assert::assertEquals(
            [
                $storedEvent->eventId(),
                $signedUp->aggregateId(),
                $signedUp->aggregateVersion() + 1,
                $signedUp->eventId(),
                $signedUp->eventId(),
                $storedEvent->recordedOn(),
                $command->metadata,
                $command->verificationCode
            ],
            [
                $storedEvent->eventId(),
                $storedEvent->aggregateId(),
                $storedEvent->aggregateVersion(),
                $storedEvent->causationId(),
                $storedEvent->correlationId(),
                $storedEvent->recordedOn(),
                $storedEvent->metadata(),
                $storedEvent->verifiedWithCode(),
            ]
        );
    }

    public function testHandleRequestedEmail(): void
    {
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $requestedEmail = SampleEvents::primaryEmailChangeRequested(
            $signedUp->aggregateId(),
            3,
            $primaryEmailVerified->eventId(),
            $signedUp->eventId()
        );

        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->save($requestedEmail);
        $eventStore->completeTransaction();

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithCallback(
                UserIdFromPrimaryEmailVerificationCode::class,
                'userIdFromPrimaryEmailVerificationCode',
                function (string $primaryEmailVerificationCode) use ($requestedEmail): ?string {
                    if ($requestedEmail->newVerificationCode() === $primaryEmailVerificationCode) {
                        return $requestedEmail->aggregateId()->id();
                    }

                    return null;
                }
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = $requestedEmail->newVerificationCode();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[3];

        if (!$storedEvent instanceof PrimaryEmailVerified) {
            throw new \Exception();
        }

        Assert::assertEquals(
            [
                $storedEvent->eventId(),
                $signedUp->aggregateId(),
                $requestedEmail->aggregateVersion() + 1,
                $requestedEmail->eventId(),
                $signedUp->eventId(),
                $storedEvent->recordedOn(),
                $command->metadata,
                $command->verificationCode
            ],
            [
                $storedEvent->eventId(),
                $storedEvent->aggregateId(),
                $storedEvent->aggregateVersion(),
                $storedEvent->causationId(),
                $storedEvent->correlationId(),
                $storedEvent->recordedOn(),
                $storedEvent->metadata(),
                $storedEvent->verifiedWithCode(),
            ]
        );
    }

    public function testNoUserFoundForCodeWhenUserIdIsNull(): void
    {
        $this->expectException(NoUserFoundForCode::class);
        $signedUp = SampleEvents::signedUp();
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->completeTransaction();

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromPrimaryEmailVerificationCode::class,
                'userIdFromPrimaryEmailVerificationCode',
                null
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = $signedUp->primaryEmailVerificationCode();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testNoUserFoundForCode(): void
    {
        $this->expectException(NoUserFoundForCode::class);
        $signedUp = SampleEvents::signedUp();
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->completeTransaction();

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromPrimaryEmailVerificationCode::class,
                'userIdFromPrimaryEmailVerificationCode',
                Id::createNew()->id()
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = $signedUp->primaryEmailVerificationCode();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testEmailIsAlreadyVerified(): void
    {
        $this->expectException(EmailIsAlreadyVerified::class);
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

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromPrimaryEmailVerificationCode::class,
                'userIdFromPrimaryEmailVerificationCode',
                $signedUp->aggregateId()->id()
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = $signedUp->primaryEmailVerificationCode();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testVerificationCodeDoesNotMatchForUnverifiedEmail(): void
    {
        $this->expectException(VerificationCodeDoesNotMatch::class);
        $signedUp = SampleEvents::signedUp();
        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->completeTransaction();

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromPrimaryEmailVerificationCode::class,
                'userIdFromPrimaryEmailVerificationCode',
                $signedUp->aggregateId()->id()
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = "ThisCodeDoesNotMatch";
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testVerificationCodeDoesNotMatchForRequestedEmail(): void
    {
        $this->expectException(VerificationCodeDoesNotMatch::class);
        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $requestedEmail = PrimaryEmailChangeRequested::new(
            Id::createNew(),
            $signedUp->aggregateId(),
            3,
            $primaryEmailVerified->eventId(),
            $signedUp->eventId(),
            new \DateTimeImmutable("now"),
            $this->mockMetadata(),
            "new_email_8as12nAjs@example.com",
            EmailVerificationCodeCreator::create(),
            $signedUp->hashedPassword()
        );

        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($primaryEmailVerified);
        $eventStore->save($requestedEmail);
        $eventStore->completeTransaction();

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromPrimaryEmailVerificationCode::class,
                'userIdFromPrimaryEmailVerificationCode',
                $signedUp->aggregateId()->id()
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = "ThisCodeDoesNotMatchTheExpectedCode";
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}

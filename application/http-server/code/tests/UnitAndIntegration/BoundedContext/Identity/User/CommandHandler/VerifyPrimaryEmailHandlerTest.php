<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\CommandHandler;

use Galeas\Api\BoundedContext\Identity\User\Command\VerifyPrimaryEmail;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\UserIdFromPrimaryEmailVerificationCode;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\VerifyPrimaryEmailHandler;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidVerificationCodes;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;

class VerifyPrimaryEmailHandlerTest extends HandlerTestBase
{
    /**
     * @test
     *
     * @throws \Exception
     */
    public function testHandleUnverifiedEmail(): void
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

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
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
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[1];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        if (
            null === $storedEvent->authorizerId() ||
            (!($storedEvent instanceof PrimaryEmailVerified)) ||
            (!($queuedEvent instanceof PrimaryEmailVerified))
        ) {
            throw new \Exception();
        }

        Assert::assertEquals(
            $storedEvent,
            $queuedEvent
        );
        Assert::assertEquals(
            $command->verificationCode,
            $storedEvent->verifiedWithCode()
        );
        Assert::assertEquals(
            $signedUp->aggregateId()->id(),
            $storedEvent->authorizerId()->id()
        );
        Assert::assertEquals(
            $signedUp->aggregateId()->id(),
            $storedEvent->aggregateId()->id()
        );
        Assert::assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
    }

    /**
     * @test
     */
    public function testHandleRequestedEmail(): void
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
        $requestedEmail = PrimaryEmailChangeRequested::fromProperties(
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
        $eventStore->save($requestedEmail);
        $eventStore->completeTransaction();

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
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
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        if (
            null === $storedEvent->authorizerId() ||
            (!($storedEvent instanceof PrimaryEmailVerified)) ||
            (!($queuedEvent instanceof PrimaryEmailVerified))
        ) {
            throw new \Exception();
        }

        Assert::assertEquals(
            $storedEvent,
            $queuedEvent
        );
        Assert::assertEquals(
            $command->verificationCode,
            $storedEvent->verifiedWithCode()
        );
        Assert::assertEquals(
            $signedUp->aggregateId()->id(),
            $storedEvent->authorizerId()->id()
        );
        Assert::assertEquals(
            $signedUp->aggregateId()->id(),
            $storedEvent->aggregateId()->id()
        );
        Assert::assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\NoUserFoundForCode
     */
    public function testNoUserFoundForCodeWhenUserIdIsNull(): void
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

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
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

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\NoUserFoundForCode
     */
    public function testNoUserFoundForCode(): void
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

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
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

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\EmailIsAlreadyVerified
     */
    public function testEmailIsAlreadyVerified(): void
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

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
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

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\VerificationCodeDoesNotMatch
     */
    public function testVerificationCodeDoesNotMatchForUnverifiedEmail(): void
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

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromPrimaryEmailVerificationCode::class,
                'userIdFromPrimaryEmailVerificationCode',
                $signedUp->aggregateId()->id()
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = ValidVerificationCodes::listValidVerificationCodes()[0];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\VerificationCodeDoesNotMatch
     */
    public function testVerificationCodeDoesNotMatchForRequestedEmail(): void
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
        $requestedEmail = PrimaryEmailChangeRequested::fromProperties(
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
        $eventStore->save($requestedEmail);
        $eventStore->completeTransaction();

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromPrimaryEmailVerificationCode::class,
                'userIdFromPrimaryEmailVerificationCode',
                $signedUp->aggregateId()->id()
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = ValidVerificationCodes::listValidVerificationCodes()[0];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}

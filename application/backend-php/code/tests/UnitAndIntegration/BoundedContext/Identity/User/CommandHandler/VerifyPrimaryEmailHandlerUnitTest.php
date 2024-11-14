<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\CommandHandler;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\AbandonedEmailRetaken;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\EmailAbandoned;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\EmailTaken;
use Galeas\Api\BoundedContext\Identity\User\Command\VerifyPrimaryEmail;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\EmailIsAlreadyVerified;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\EmailIsTaken;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\NoVerifiableUserFoundForCode;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\UsernameIsTaken;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\VerificationCodeDoesNotMatch;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\VerifyPrimaryEmailHandler;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\UserIdFromPrimaryEmailVerificationCode;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\IsUsernameTaken;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveCreation\Email\EmailVerificationCodeCreator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerUnitTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class VerifyPrimaryEmailHandlerUnitTest extends HandlerUnitTest
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
                static function (string $primaryEmailVerificationCode) use ($signedUp): ?string {
                    if ($signedUp->primaryEmailVerificationCode() === $primaryEmailVerificationCode) {
                        return $signedUp->aggregateId()->id();
                    }

                    return null;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = $signedUp->primaryEmailVerificationCode();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $primaryEmailVerified = $this->getInMemoryEventStore()->storedEvents()[1];

        if (!$primaryEmailVerified instanceof PrimaryEmailVerified) {
            throw new \Exception();
        }

        Assert::assertEquals(
            [
                $primaryEmailVerified->eventId(),
                $signedUp->aggregateId(),
                $signedUp->aggregateVersion() + 1,
                $signedUp->eventId(),
                $signedUp->eventId(),
                $primaryEmailVerified->recordedOn(),
                $command->metadata,
                $command->verificationCode,
            ],
            [
                $primaryEmailVerified->eventId(),
                $primaryEmailVerified->aggregateId(),
                $primaryEmailVerified->aggregateVersion(),
                $primaryEmailVerified->causationId(),
                $primaryEmailVerified->correlationId(),
                $primaryEmailVerified->recordedOn(),
                $primaryEmailVerified->metadata(),
                $primaryEmailVerified->verifiedWithCode(),
            ]
        );

        $newVerifiedEmailTaken = $this->getInMemoryEventStore()->storedEvents()[2];
        if (!$newVerifiedEmailTaken instanceof EmailTaken) {
            throw new \Exception();
        }

        Assert::assertEquals(
            [
                $newVerifiedEmailTaken->eventId(),
                Id::createNewByHashing(
                    'Identity_TakenEmail:'.strtolower($signedUp->primaryEmail())
                ),
                1,
                $newVerifiedEmailTaken->causationId(),
                $newVerifiedEmailTaken->correlationId(),
                $newVerifiedEmailTaken->recordedOn(),
                $command->metadata,
                strtolower($signedUp->primaryEmail()),
                $signedUp->aggregateId(),
            ],
            [
                $newVerifiedEmailTaken->eventId(),
                $newVerifiedEmailTaken->aggregateId(),
                $newVerifiedEmailTaken->aggregateVersion(),
                $newVerifiedEmailTaken->causationId(),
                $newVerifiedEmailTaken->correlationId(),
                $newVerifiedEmailTaken->recordedOn(),
                $newVerifiedEmailTaken->metadata(),
                $newVerifiedEmailTaken->takenEmailInLowercase(),
                $newVerifiedEmailTaken->takenByUser(),
            ]
        );
    }

    public function testHandleUnverifiedEmailVerifiedForAbandonedEmail(): void
    {
        $signedUp = SampleEvents::signedUp();
        $emailTaken = SampleEvents::emailTaken(
            $signedUp->primaryEmail(),
            $signedUp->aggregateId()
        );
        $emailAbandoned = SampleEvents::emailAbandoned(
            $emailTaken->aggregateId(),
            2,
            $emailTaken->eventId(),
            $emailTaken->eventId()
        );

        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($emailTaken);
        $eventStore->save($emailAbandoned);
        $eventStore->completeTransaction();

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithCallback(
                UserIdFromPrimaryEmailVerificationCode::class,
                'userIdFromPrimaryEmailVerificationCode',
                static function (string $primaryEmailVerificationCode) use ($signedUp): ?string {
                    if ($signedUp->primaryEmailVerificationCode() === $primaryEmailVerificationCode) {
                        return $signedUp->aggregateId()->id();
                    }

                    return null;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = $signedUp->primaryEmailVerificationCode();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $primaryEmailVerified = $this->getInMemoryEventStore()->storedEvents()[3];

        if (!$primaryEmailVerified instanceof PrimaryEmailVerified) {
            throw new \Exception();
        }

        Assert::assertEquals(
            [
                $primaryEmailVerified->eventId(),
                $signedUp->aggregateId(),
                $signedUp->aggregateVersion() + 1,
                $signedUp->eventId(),
                $signedUp->eventId(),
                $primaryEmailVerified->recordedOn(),
                $command->metadata,
                $command->verificationCode,
            ],
            [
                $primaryEmailVerified->eventId(),
                $primaryEmailVerified->aggregateId(),
                $primaryEmailVerified->aggregateVersion(),
                $primaryEmailVerified->causationId(),
                $primaryEmailVerified->correlationId(),
                $primaryEmailVerified->recordedOn(),
                $primaryEmailVerified->metadata(),
                $primaryEmailVerified->verifiedWithCode(),
            ]
        );

        $abandonedEmailRetaken = $this->getInMemoryEventStore()->storedEvents()[4];
        if (!$abandonedEmailRetaken instanceof AbandonedEmailRetaken) {
            throw new \Exception();
        }

        Assert::assertEquals(
            [
                $abandonedEmailRetaken->eventId(),
                $emailTaken->aggregateId(),
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

    public function testHandleUnverifiedEmailIsTaken(): void
    {
        $signedUp = SampleEvents::signedUp();
        $emailTaken = SampleEvents::emailTaken(
            $signedUp->primaryEmail(),
            $signedUp->aggregateId()
        );

        $eventStore = $this->getInMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->save($signedUp);
        $eventStore->save($emailTaken);
        $eventStore->completeTransaction();

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithCallback(
                UserIdFromPrimaryEmailVerificationCode::class,
                'userIdFromPrimaryEmailVerificationCode',
                static function (string $primaryEmailVerificationCode) use ($signedUp): ?string {
                    if ($signedUp->primaryEmailVerificationCode() === $primaryEmailVerificationCode) {
                        return $signedUp->aggregateId()->id();
                    }

                    return null;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = $signedUp->primaryEmailVerificationCode();
        $command->metadata = $this->mockMetadata();

        $this->expectException(EmailIsTaken::class);
        $handler->handle($command);
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
        $verifiedEmailTaken = SampleEvents::emailTaken(
            $signedUp->primaryEmail(),
            $signedUp->aggregateId(),
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
        $eventStore->save($verifiedEmailTaken);
        $eventStore->save($requestedEmail);
        $eventStore->completeTransaction();

        $handler = new VerifyPrimaryEmailHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithCallback(
                UserIdFromPrimaryEmailVerificationCode::class,
                'userIdFromPrimaryEmailVerificationCode',
                static function (string $primaryEmailVerificationCode) use ($requestedEmail): ?string {
                    if ($requestedEmail->newVerificationCode() === $primaryEmailVerificationCode) {
                        return $requestedEmail->aggregateId()->id();
                    }

                    return null;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = $requestedEmail->newVerificationCode();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $primaryEmailVerified = $this->getInMemoryEventStore()->storedEvents()[4];

        if (!$primaryEmailVerified instanceof PrimaryEmailVerified) {
            throw new \Exception();
        }

        Assert::assertEquals(
            [
                $primaryEmailVerified->eventId(),
                $signedUp->aggregateId(),
                $requestedEmail->aggregateVersion() + 1,
                $requestedEmail->eventId(),
                $signedUp->eventId(),
                $primaryEmailVerified->recordedOn(),
                $command->metadata,
                $command->verificationCode,
            ],
            [
                $primaryEmailVerified->eventId(),
                $primaryEmailVerified->aggregateId(),
                $primaryEmailVerified->aggregateVersion(),
                $primaryEmailVerified->causationId(),
                $primaryEmailVerified->correlationId(),
                $primaryEmailVerified->recordedOn(),
                $primaryEmailVerified->metadata(),
                $primaryEmailVerified->verifiedWithCode(),
            ]
        );

        $newVerifiedEmailTaken = $this->getInMemoryEventStore()->storedEvents()[5];
        if (!$newVerifiedEmailTaken instanceof EmailTaken) {
            throw new \Exception();
        }

        Assert::assertEquals(
            [
                $newVerifiedEmailTaken->eventId(),
                Id::createNewByHashing(
                    'Identity_TakenEmail:'.strtolower($requestedEmail->newEmailRequested())
                ),
                1,
                $newVerifiedEmailTaken->causationId(),
                $newVerifiedEmailTaken->correlationId(),
                $newVerifiedEmailTaken->recordedOn(),
                $command->metadata,
                $requestedEmail->newEmailRequested(),
                $signedUp->aggregateId(),
            ],
            [
                $newVerifiedEmailTaken->eventId(),
                $newVerifiedEmailTaken->aggregateId(),
                $newVerifiedEmailTaken->aggregateVersion(),
                $newVerifiedEmailTaken->causationId(),
                $newVerifiedEmailTaken->correlationId(),
                $newVerifiedEmailTaken->recordedOn(),
                $newVerifiedEmailTaken->metadata(),
                $newVerifiedEmailTaken->takenEmailInLowercase(),
                $newVerifiedEmailTaken->takenByUser(),
            ]
        );

        $oldVerifiedEmailAbandoned = $this->getInMemoryEventStore()->storedEvents()[6];
        if (!$oldVerifiedEmailAbandoned instanceof EmailAbandoned) {
            throw new \Exception();
        }

        Assert::assertEquals(
            [
                $oldVerifiedEmailAbandoned->eventId(),
                $verifiedEmailTaken->aggregateId(),
                2,
                $verifiedEmailTaken->eventId(),
                $verifiedEmailTaken->eventId(),
                $oldVerifiedEmailAbandoned->recordedOn(),
                $command->metadata,
            ],
            [
                $oldVerifiedEmailAbandoned->eventId(),
                $oldVerifiedEmailAbandoned->aggregateId(),
                $oldVerifiedEmailAbandoned->aggregateVersion(),
                $oldVerifiedEmailAbandoned->causationId(),
                $oldVerifiedEmailAbandoned->correlationId(),
                $oldVerifiedEmailAbandoned->recordedOn(),
                $oldVerifiedEmailAbandoned->metadata(),
            ]
        );
    }

    public function testNoVerifiableUserFoundForCodeWhenUserIdIsNull(): void
    {
        $this->expectException(NoVerifiableUserFoundForCode::class);
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
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = $signedUp->primaryEmailVerificationCode();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testNoVerifiableUserFoundForCode(): void
    {
        $this->expectException(NoVerifiableUserFoundForCode::class);
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
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
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
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = $signedUp->primaryEmailVerificationCode();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testUsernameIsTaken(): void
    {
        $this->expectException(UsernameIsTaken::class);
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
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                true
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
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = 'ThisCodeDoesNotMatch';
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
            new \DateTimeImmutable('now'),
            $this->mockMetadata(),
            'new_email_8as12nAjs@example.com',
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
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new VerifyPrimaryEmail();
        $command->verificationCode = 'ThisCodeDoesNotMatchTheExpectedCode';
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}

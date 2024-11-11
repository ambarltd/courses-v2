<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\CommandHandler;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\AbandonedEmailRetaken;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\EmailTaken;
use Galeas\Api\BoundedContext\Identity\User\Command\SignUp;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\EmailIsTaken;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\InvalidEmail;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\InvalidPassword;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\InvalidUsername;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\SignUpHandler;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\TermsAreNotAgreedTo;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\UsernameIsTaken;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\IsUsernameTaken;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerUnitTest;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\InvalidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\InvalidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\InvalidUsernames;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SignUpHandlerUnitTest extends HandlerUnitTest
{
    public function testHandle(): void
    {
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithCallback(
                IsUsernameTaken::class,
                'isUsernameTaken',
                static function (string $username): bool {
                    if (ValidUsernames::listValidUsernames()[0] === $username) {
                        return false;
                    }

                    return true;
                }
            )
        );

        $command = new SignUp();
        $command->primaryEmail = ValidEmails::listValidEmails()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $response = $handler->handle($command);

        /** @var SignedUp $signedUp */
        $signedUp = $this->getInMemoryEventStore()->storedEvents()[0];

        Assert::assertTrue(
            password_verify(
                $command->password,
                $signedUp->hashedPassword()
            )
        );
        Assert::assertEquals(
            [
                'userId' => $signedUp->aggregateId()->id(),
            ],
            $response
        );

        Assert::assertEquals(
            [
                $signedUp->eventId(),
                $signedUp->aggregateId(),
                1,
                $signedUp->eventId(),
                $signedUp->eventId(),
                $signedUp->recordedOn(),
                $command->metadata,
                $command->primaryEmail,
                $signedUp->primaryEmailVerificationCode(),
                $signedUp->hashedPassword(),
                $command->username,
                $command->termsOfUseAccepted,
            ],
            [
                $signedUp->eventId(),
                $signedUp->aggregateId(),
                $signedUp->aggregateVersion(),
                $signedUp->causationId(),
                $signedUp->correlationId(),
                $signedUp->recordedOn(),
                $signedUp->metadata(),
                $signedUp->primaryEmail(),
                $signedUp->primaryEmailVerificationCode(),
                $signedUp->hashedPassword(),
                $signedUp->username(),
                $signedUp->termsOfUseAccepted(),
            ]
        );

        /** @var EmailTaken $emailTaken */
        $emailTaken = $this->getInMemoryEventStore()->storedEvents()[1];

        Assert::assertEquals(
            [
                $emailTaken->eventId(),
                Id::createNewByHashing(
                    'Identity_TakenEmail:'.strtolower($command->primaryEmail)
                ),
                1,
                $emailTaken->eventId(),
                $emailTaken->eventId(),
                $emailTaken->recordedOn(),
                $command->metadata,
                strtolower($command->primaryEmail),
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

    public function testHandleAbandonedTakenEmail(): void
    {
        $emailTaken = SampleEvents::emailTaken(
            SampleEvents::signedUp()->primaryEmail(),
            Id::createNew(),
        );
        $emailAbandoned = SampleEvents::emailAbandoned(
            $emailTaken->aggregateId(),
            2,
            $emailTaken->eventId(),
            $emailTaken->eventId(),
        );
        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($emailTaken);
        $this->getInMemoryEventStore()->save($emailAbandoned);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithCallback(
                IsUsernameTaken::class,
                'isUsernameTaken',
                static function (string $username): bool {
                    if (ValidUsernames::listValidUsernames()[0] === $username) {
                        return false;
                    }

                    return true;
                }
            )
        );

        $command = new SignUp();
        $command->primaryEmail = SampleEvents::signedUp()->primaryEmail();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $response = $handler->handle($command);

        /** @var SignedUp $signedUp */
        $signedUp = $this->getInMemoryEventStore()->storedEvents()[2];

        Assert::assertTrue(
            password_verify(
                $command->password,
                $signedUp->hashedPassword()
            )
        );
        Assert::assertEquals(
            [
                'userId' => $signedUp->aggregateId()->id(),
            ],
            $response
        );

        Assert::assertEquals(
            [
                $signedUp->eventId(),
                $signedUp->aggregateId(),
                1,
                $signedUp->eventId(),
                $signedUp->eventId(),
                $signedUp->recordedOn(),
                $command->metadata,
                $command->primaryEmail,
                $signedUp->primaryEmailVerificationCode(),
                $signedUp->hashedPassword(),
                $command->username,
                $command->termsOfUseAccepted,
            ],
            [
                $signedUp->eventId(),
                $signedUp->aggregateId(),
                $signedUp->aggregateVersion(),
                $signedUp->causationId(),
                $signedUp->correlationId(),
                $signedUp->recordedOn(),
                $signedUp->metadata(),
                $signedUp->primaryEmail(),
                $signedUp->primaryEmailVerificationCode(),
                $signedUp->hashedPassword(),
                $signedUp->username(),
                $signedUp->termsOfUseAccepted(),
            ]
        );

        /** @var AbandonedEmailRetaken $abandonedEmailRetaken */
        $abandonedEmailRetaken = $this->getInMemoryEventStore()->storedEvents()[3];

        Assert::assertEquals(
            [
                $abandonedEmailRetaken->eventId(),
                $emailAbandoned->aggregateId(),
                3,
                $emailAbandoned->eventId(),
                $emailAbandoned->correlationId(),
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

    public function testInvalidEmail(): void
    {
        $this->expectException(InvalidEmail::class);
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new SignUp();
        $command->primaryEmail = InvalidEmails::listInvalidEmails()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testInvalidPassword(): void
    {
        $this->expectException(InvalidPassword::class);
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new SignUp();
        $command->primaryEmail = ValidEmails::listValidEmails()[0];
        $command->password = InvalidPasswords::listInvalidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testInvalidUsername(): void
    {
        $this->expectException(InvalidUsername::class);
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new SignUp();
        $command->primaryEmail = ValidEmails::listValidEmails()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = InvalidUsernames::listInvalidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testTermsAreNotAgreedTo(): void
    {
        $this->expectException(TermsAreNotAgreedTo::class);
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new SignUp();
        $command->primaryEmail = ValidEmails::listValidEmails()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = false;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testEmailIsTaken(): void
    {
        $this->expectException(EmailIsTaken::class);
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );
        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save(SampleEvents::emailTaken(
            SampleEvents::signedUp()->primaryEmail(),
            Id::createNew(),
        ));
        $this->getInMemoryEventStore()->completeTransaction();

        $command = new SignUp();
        $command->primaryEmail = SampleEvents::signedUp()->primaryEmail();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testEmailIsRetaken(): void
    {
        $this->expectException(EmailIsTaken::class);
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );
        $this->getInMemoryEventStore()->beginTransaction();
        $emailTaken = SampleEvents::emailTaken(
            SampleEvents::signedUp()->primaryEmail(),
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
        $this->getInMemoryEventStore()->save($emailTaken);
        $this->getInMemoryEventStore()->save($emailAbandoned);
        $this->getInMemoryEventStore()->save($abandonedEmailRetaken);
        $this->getInMemoryEventStore()->completeTransaction();

        $command = new SignUp();
        $command->primaryEmail = SampleEvents::signedUp()->primaryEmail();
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    public function testUsernameIsTaken(): void
    {
        $this->expectException(UsernameIsTaken::class);
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                true
            )
        );

        $command = new SignUp();
        $command->primaryEmail = ValidEmails::listValidEmails()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}

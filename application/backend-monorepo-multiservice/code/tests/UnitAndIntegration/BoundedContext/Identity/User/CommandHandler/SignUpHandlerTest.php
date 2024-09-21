<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\CommandHandler;

use Galeas\Api\BoundedContext\Identity\User\Command\SignUp;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\EmailIsTaken;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\InvalidEmail;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\InvalidPassword;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\InvalidUsername;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\IsEmailTaken;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\IsUsernameTaken;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\SignUpHandler;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\TermsAreNotAgreedTo;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\UsernameIsTaken;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\InvalidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\InvalidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\InvalidUsernames;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;

class SignUpHandlerTest extends HandlerTestBase
{
    public function testHandle(): void
    {
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithCallback(
                IsEmailTaken::class,
                'isEmailTaken',
                function (string $email): bool {
                    if (ValidEmails::listValidEmails()[0] === $email) {
                        return false;
                    }

                    return true;
                }
            ),
            $this->mockForCommandHandlerWithCallback(
                IsUsernameTaken::class,
                'isUsernameTaken',
                function (string $username): bool {
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

        /** @var SignedUp $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[0];

        Assert::assertTrue(
            password_verify(
                $command->password,
                $storedEvent->hashedPassword()
            )
        );
        Assert::assertEquals(
            [
                'userId' => $storedEvent->aggregateId()->id(),
            ],
            $response
        );

        Assert::assertEquals(
            [
                $storedEvent->eventId(),
                $storedEvent->aggregateId(),
                1,
                $storedEvent->eventId(),
                $storedEvent->eventId(),
                $storedEvent->recordedOn(),
                $command->metadata,
                $storedEvent->primaryEmail(),
                $storedEvent->primaryEmailVerificationCode(),
                $storedEvent->hashedPassword(),
                $command->username,
                $command->termsOfUseAccepted
            ],
            [
                $storedEvent->eventId(),
                $storedEvent->aggregateId(),
                $storedEvent->aggregateVersion(),
                $storedEvent->causationId(),
                $storedEvent->correlationId(),
                $storedEvent->recordedOn(),
                $storedEvent->metadata(),
                $storedEvent->primaryEmail(),
                $storedEvent->primaryEmailVerificationCode(),
                $storedEvent->hashedPassword(),
                $storedEvent->username(),
                $storedEvent->termsOfUseAccepted(),
            ]
        );
    }

    public function testInvalidEmail(): void
    {
        $this->expectException(InvalidEmail::class);
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            ),
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
                IsEmailTaken::class,
                'isEmailTaken',
                false
            ),
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
                IsEmailTaken::class,
                'isEmailTaken',
                false
            ),
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
                IsEmailTaken::class,
                'isEmailTaken',
                false
            ),
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
                IsEmailTaken::class,
                'isEmailTaken',
                true
            ),
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
                IsEmailTaken::class,
                'isEmailTaken',
                false
            ),
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

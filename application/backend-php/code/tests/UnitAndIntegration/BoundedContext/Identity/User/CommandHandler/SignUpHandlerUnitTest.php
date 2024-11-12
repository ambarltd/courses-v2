<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\CommandHandler;

use Galeas\Api\BoundedContext\Identity\User\Command\SignUp;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\InvalidEmail;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\InvalidPassword;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\InvalidUsername;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\SignUpHandler;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\TermsAreNotAgreedTo;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerUnitTest;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\InvalidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\InvalidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\InvalidUsernames;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;

class SignUpHandlerUnitTest extends HandlerUnitTest
{
    public function testHandle(): void
    {
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
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
    }

    public function testInvalidEmail(): void
    {
        $this->expectException(InvalidEmail::class);
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
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
        );

        $command = new SignUp();
        $command->primaryEmail = ValidEmails::listValidEmails()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = false;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}

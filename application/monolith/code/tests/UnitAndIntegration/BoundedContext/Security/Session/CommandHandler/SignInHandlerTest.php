<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\CommandHandler;

use Galeas\Api\BoundedContext\Security\Session\Command\SignIn;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn\HashedPasswordFromUserId;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn\SignInHandler;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn\UserIdFromSignInEmail;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn\UserIdFromSignInUsername;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveValidation\Session\SessionTokenValidator;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip\InvalidIpsV4AndV6;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip\ValidIpsV4AndV6;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Session\InvalidDeviceLabels;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Session\ValidDeviceLabels;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;

class SignInHandlerTest extends HandlerTestBase
{
    /**
     * @test
     *
     * @throws \Exception
     */
    public function testHandleWithUsername(): void
    {
        $userIdFromSignInUsername = Id::createNew();
        $hashedPassword = password_hash(ValidPasswords::listValidPasswords()[0], PASSWORD_BCRYPT, ['cost' => 11]);

        if (!is_string($hashedPassword)) {
            throw new \Exception();
        }

        $handler = new SignInHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithCallback(
                UserIdFromSignInUsername::class,
                'userIdFromSignInUsername',
                function (string $username) use ($userIdFromSignInUsername): ?string {
                    if ($username === ValidUsernames::listValidUsernames()[0]) {
                        return $userIdFromSignInUsername->id();
                    }

                    return null;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromSignInEmail::class,
                'userIdFromSignInEmail',
                null
            ),
            $this->mockForCommandHandlerWithCallback(
                HashedPasswordFromUserId::class,
                'hashedPasswordFromUserId',
                function (string $userId) use ($userIdFromSignInUsername, $hashedPassword): ?string {
                    if ($userId === $userIdFromSignInUsername->id()) {
                        return $hashedPassword;
                    }

                    return null;
                }
            )
        );

        $command = new SignIn();
        $command->withUsernameOrEmail = ValidUsernames::listValidUsernames()[0];
        $command->metadata = $this->mockMetadata();
        $command->byDeviceLabel = ValidDeviceLabels::listValidDeviceLabels()[0];
        $command->withPassword = ValidPasswords::listValidPasswords()[0];
        $command->withIp = ValidIpsV4AndV6::listValidIps()[0];

        $response = $handler->handle($command);

        /** @var SignedIn $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[0];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        $this->assertEquals($storedEvent, $queuedEvent);
        $this->assertEquals(
            $command->withUsernameOrEmail,
            $storedEvent->withUsername()
        );
        $this->assertEquals(
            null,
            $storedEvent->withEmail()
        );
        $this->assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
        $this->assertTrue(
            password_verify(
                $command->withPassword,
                $storedEvent->withHashedPassword()
            )
        );
        $this->assertEquals(
            $command->withIp,
            $storedEvent->withIp()
        );
        $this->assertTrue(
            SessionTokenValidator::isValid(
                $storedEvent->sessionTokenCreated()
            )
        );

        $this->assertEquals(
            [
                'sessionTokenCreated' => $storedEvent->sessionTokenCreated(),
            ],
            $response
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testHandleWithEmail(): void
    {
        $userIdFromSignInEmail = Id::createNew();
        $hashedPassword = password_hash(ValidPasswords::listValidPasswords()[0], PASSWORD_BCRYPT, ['cost' => 11]);

        if (!is_string($hashedPassword)) {
            throw new \Exception();
        }

        $handler = new SignInHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromSignInUsername::class,
                'userIdFromSignInUsername',
                null
            ),
            $this->mockForCommandHandlerWithCallback(
                UserIdFromSignInEmail::class,
                'userIdFromSignInEmail',
                function (string $username) use ($userIdFromSignInEmail): ?string {
                    if ($username === ValidUsernames::listValidUsernames()[0]) {
                        return $userIdFromSignInEmail->id();
                    }

                    return null;
                }
            ),
            $this->mockForCommandHandlerWithCallback(
                HashedPasswordFromUserId::class,
                'hashedPasswordFromUserId',
                function (string $userId) use ($userIdFromSignInEmail, $hashedPassword): ?string {
                    if ($userId === $userIdFromSignInEmail->id()) {
                        return $hashedPassword;
                    }

                    return null;
                }
            )
        );

        $command = new SignIn();
        $command->withUsernameOrEmail = ValidUsernames::listValidUsernames()[0];
        $command->metadata = $this->mockMetadata();
        $command->byDeviceLabel = ValidDeviceLabels::listValidDeviceLabels()[0];
        $command->withPassword = ValidPasswords::listValidPasswords()[0];
        $command->withIp = ValidIpsV4AndV6::listValidIps()[0];

        $response = $handler->handle($command);

        /** @var SignedIn $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[0];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        $this->assertEquals($storedEvent, $queuedEvent);
        $this->assertEquals(
            null,
            $storedEvent->withUsername()
        );
        $this->assertEquals(
            $command->withUsernameOrEmail,
            $storedEvent->withEmail()
        );
        $this->assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
        $this->assertTrue(
            password_verify(
                $command->withPassword,
                $storedEvent->withHashedPassword()
            )
        );
        $this->assertEquals(
            $command->withIp,
            $storedEvent->withIp()
        );
        $this->assertTrue(
            SessionTokenValidator::isValid(
                $storedEvent->sessionTokenCreated()
            )
        );

        $this->assertEquals(
            [
                'sessionTokenCreated' => $storedEvent->sessionTokenCreated(),
            ],
            $response
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn\UserNotFound
     */
    public function testUserNotFound(): void
    {
        $hashedPassword = password_hash(ValidPasswords::listValidPasswords()[0], PASSWORD_BCRYPT, ['cost' => 4]);

        $handler = new SignInHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromSignInUsername::class,
                'userIdFromSignInUsername',
                null
            ),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromSignInEmail::class,
                'userIdFromSignInEmail',
                null
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HashedPasswordFromUserId::class,
                'hashedPasswordFromUserId',
                $hashedPassword
            )
        );

        $command = new SignIn();
        $command->withUsernameOrEmail = ValidUsernames::listValidUsernames()[0];
        $command->metadata = $this->mockMetadata();
        $command->byDeviceLabel = ValidDeviceLabels::listValidDeviceLabels()[0];
        $command->withPassword = ValidPasswords::listValidPasswords()[0];
        $command->withIp = ValidIpsV4AndV6::listValidIps()[0];

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn\NoPasswordFound
     */
    public function testNoPasswordFound(): void
    {
        $userIdFromSignInUsername = Id::createNew();

        $handler = new SignInHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromSignInUsername::class,
                'userIdFromSignInUsername',
                $userIdFromSignInUsername->id()
            ),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromSignInEmail::class,
                'userIdFromSignInEmail',
                null
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HashedPasswordFromUserId::class,
                'hashedPasswordFromUserId',
                null
            )
        );

        $command = new SignIn();
        $command->withUsernameOrEmail = ValidUsernames::listValidUsernames()[0];
        $command->metadata = $this->mockMetadata();
        $command->byDeviceLabel = ValidDeviceLabels::listValidDeviceLabels()[0];
        $command->withPassword = ValidPasswords::listValidPasswords()[0];
        $command->withIp = ValidIpsV4AndV6::listValidIps()[0];

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn\InvalidPassword
     */
    public function testInvalidPassword(): void
    {
        $userIdFromSignInUsername = Id::createNew();
        $hashedPassword = password_hash(ValidPasswords::listValidPasswords()[0], PASSWORD_BCRYPT, ['cost' => 4]);

        $handler = new SignInHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromSignInUsername::class,
                'userIdFromSignInUsername',
                $userIdFromSignInUsername->id()
            ),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromSignInEmail::class,
                'userIdFromSignInEmail',
                null
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HashedPasswordFromUserId::class,
                'hashedPasswordFromUserId',
                $hashedPassword
            )
        );

        $command = new SignIn();
        $command->withUsernameOrEmail = ValidUsernames::listValidUsernames()[0];
        $command->metadata = $this->mockMetadata();
        $command->byDeviceLabel = ValidDeviceLabels::listValidDeviceLabels()[0];
        $command->withPassword = ValidPasswords::listValidPasswords()[1];
        $command->withIp = ValidIpsV4AndV6::listValidIps()[0];

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn\InvalidDeviceLabel
     */
    public function testInvalidDeviceLabel(): void
    {
        $userIdFromSignInUsername = Id::createNew();
        $hashedPassword = password_hash(ValidPasswords::listValidPasswords()[0], PASSWORD_BCRYPT, ['cost' => 4]);

        $handler = new SignInHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromSignInUsername::class,
                'userIdFromSignInUsername',
                $userIdFromSignInUsername->id()
            ),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromSignInEmail::class,
                'userIdFromSignInEmail',
                null
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HashedPasswordFromUserId::class,
                'hashedPasswordFromUserId',
                $hashedPassword
            )
        );

        $command = new SignIn();
        $command->withUsernameOrEmail = ValidUsernames::listValidUsernames()[0];
        $command->metadata = $this->mockMetadata();
        $command->byDeviceLabel = InvalidDeviceLabels::listInvalidDeviceLabels()[0];
        $command->withPassword = ValidPasswords::listValidPasswords()[0];
        $command->withIp = ValidIpsV4AndV6::listValidIps()[0];

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn\InvalidIp
     */
    public function testInvalidIp(): void
    {
        $userIdFromSignInUsername = Id::createNew();
        $hashedPassword = password_hash(ValidPasswords::listValidPasswords()[0], PASSWORD_BCRYPT, ['cost' => 4]);

        $handler = new SignInHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromSignInUsername::class,
                'userIdFromSignInUsername',
                $userIdFromSignInUsername->id()
            ),
            $this->mockForCommandHandlerWithReturnValue(
                UserIdFromSignInEmail::class,
                'userIdFromSignInEmail',
                null
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HashedPasswordFromUserId::class,
                'hashedPasswordFromUserId',
                $hashedPassword
            )
        );

        $command = new SignIn();
        $command->withUsernameOrEmail = ValidUsernames::listValidUsernames()[0];
        $command->metadata = $this->mockMetadata();
        $command->byDeviceLabel = ValidDeviceLabels::listValidDeviceLabels()[0];
        $command->withPassword = ValidPasswords::listValidPasswords()[0];
        $command->withIp = InvalidIpsV4AndV6::listInvalidIps()[0];

        $handler->handle($command);
    }
}

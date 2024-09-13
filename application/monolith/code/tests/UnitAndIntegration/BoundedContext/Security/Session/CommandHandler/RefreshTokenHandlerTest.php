<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\CommandHandler;

use Galeas\Api\BoundedContext\Security\Session\Command\RefreshToken;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\RefreshTokenHandler;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\SessionIdFromSessionToken;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\Common\Id\Id;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip\InvalidIpsV4AndV6;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip\ValidIpsV4AndV6;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidBCryptHashes;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Session\ValidDeviceLabels;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;

class RefreshTokenHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testHandle(): void
    {
        $signedIn = SignedIn::fromProperties(
            $this->mockMetadata(),
            Id::createNew(),
            ValidUsernames::listValidUsernames()[0],
            null,
            ValidBCryptHashes::listValidBCryptHashes()[0],
            ValidDeviceLabels::listValidDeviceLabels()[0],
            ValidIpsV4AndV6::listValidIps()[0]
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($signedIn);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new RefreshTokenHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithCallback(
                SessionIdFromSessionToken::class,
                'sessionIdFromSessionToken',
                function (string $sessionToken) use ($signedIn): ?string {
                    if ($sessionToken === $signedIn->sessionTokenCreated()) {
                        return $signedIn->aggregateId()->id();
                    }

                    return null;
                }
            )
        );

        $command = new RefreshToken();
        $command->metadata = $this->mockMetadata();
        $command->authorizerId = $signedIn->authorizerId()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = ValidIpsV4AndV6::listValidIps()[1];

        $response = $handler->handle($command);

        /** @var TokenRefreshed $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[1];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        $this->assertEquals($storedEvent, $queuedEvent);
        $this->assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
        $this->assertEquals(
            $command->authorizerId,
            $storedEvent->authorizerId()->id()
        );
        $this->assertEquals(
            $command->withSessionToken,
            $storedEvent->withExistingSessionToken()
        );
        $this->assertNotEquals(
            $command->withSessionToken,
            $storedEvent->refreshedSessionToken()
        );
        $this->assertEquals(
            $command->withIp,
            $storedEvent->withIp()
        );
        $this->assertEquals(
            $signedIn->aggregateId(),
            $storedEvent->aggregateId()
        );

        $this->assertEquals(
            [
                'refreshedSessionToken' => $storedEvent->refreshedSessionToken(),
            ],
            $response
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\InvalidIp
     */
    public function testInvalidIp(): void
    {
        $signedIn = SignedIn::fromProperties(
            $this->mockMetadata(),
            Id::createNew(),
            ValidUsernames::listValidUsernames()[0],
            null,
            ValidBCryptHashes::listValidBCryptHashes()[0],
            ValidDeviceLabels::listValidDeviceLabels()[0],
            ValidIpsV4AndV6::listValidIps()[0]
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($signedIn);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new RefreshTokenHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithCallback(
                SessionIdFromSessionToken::class,
                'sessionIdFromSessionToken',
                function (string $sessionToken) use ($signedIn): ?string {
                    if ($sessionToken === $signedIn->sessionTokenCreated()) {
                        return $signedIn->aggregateId()->id();
                    }

                    return null;
                }
            )
        );

        $command = new RefreshToken();
        $command->metadata = $this->mockMetadata();
        $command->authorizerId = $signedIn->authorizerId()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = InvalidIpsV4AndV6::listInvalidIps()[1];

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\NoSessionFound
     */
    public function testNoSessionFound(): void
    {
        $signedIn = SignedIn::fromProperties(
            $this->mockMetadata(),
            Id::createNew(),
            ValidUsernames::listValidUsernames()[0],
            null,
            ValidBCryptHashes::listValidBCryptHashes()[0],
            ValidDeviceLabels::listValidDeviceLabels()[0],
            ValidIpsV4AndV6::listValidIps()[0]
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($signedIn);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new RefreshTokenHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithCallback(
                SessionIdFromSessionToken::class,
                'sessionIdFromSessionToken',
                function (string $sessionToken) use ($signedIn): ?string {
                    if ($sessionToken === $signedIn->sessionTokenCreated()) {
                        return $signedIn->aggregateId()->id();
                    }

                    return null;
                }
            )
        );

        $command = new RefreshToken();
        $command->metadata = $this->mockMetadata();
        $command->authorizerId = $signedIn->authorizerId()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated().'extra_characters';
        $command->withIp = InvalidIpsV4AndV6::listInvalidIps()[1];

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\SessionTokenDoesNotMatch
     */
    public function testSessionTokenDoesNotMatch(): void
    {
        $signedIn = SignedIn::fromProperties(
            $this->mockMetadata(),
            Id::createNew(),
            ValidUsernames::listValidUsernames()[0],
            null,
            ValidBCryptHashes::listValidBCryptHashes()[0],
            ValidDeviceLabels::listValidDeviceLabels()[0],
            ValidIpsV4AndV6::listValidIps()[0]
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($signedIn);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new RefreshTokenHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                SessionIdFromSessionToken::class,
                'sessionIdFromSessionToken',
                $signedIn->aggregateId()->id()
            )
        );

        $command = new RefreshToken();
        $command->metadata = $this->mockMetadata();
        $command->authorizerId = $signedIn->authorizerId()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated().'extra_characters';
        $command->withIp = InvalidIpsV4AndV6::listInvalidIps()[1];

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\SessionUserDoesNotMatch
     */
    public function testSessionUserDoesNotMatch(): void
    {
        $signedIn = SignedIn::fromProperties(
            $this->mockMetadata(),
            Id::createNew(),
            ValidUsernames::listValidUsernames()[0],
            null,
            ValidBCryptHashes::listValidBCryptHashes()[0],
            ValidDeviceLabels::listValidDeviceLabels()[0],
            ValidIpsV4AndV6::listValidIps()[0]
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($signedIn);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new RefreshTokenHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                SessionIdFromSessionToken::class,
                'sessionIdFromSessionToken',
                $signedIn->aggregateId()->id()
            )
        );

        $command = new RefreshToken();
        $command->metadata = $this->mockMetadata();
        $command->authorizerId = Id::createNew()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = InvalidIpsV4AndV6::listInvalidIps()[1];

        $handler->handle($command);
    }
}

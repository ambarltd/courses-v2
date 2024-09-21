<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\CommandHandler;

use Galeas\Api\BoundedContext\Security\Session\Command\RefreshToken;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\AlreadySignedOut;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\InvalidIp;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\NoSessionFound;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\RefreshTokenHandler;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\SessionIdFromSessionToken;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\SessionTokenDoesNotMatch;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\SessionUserDoesNotMatch;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip\InvalidIpsV4AndV6;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip\ValidIpsV4AndV6;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidBCryptHashes;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Session\ValidDeviceLabels;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class RefreshTokenHandlerTest extends HandlerTestBase
{
    public function testHandle(): void
    {
        $signedIn = SampleEvents::signedIn();

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
        $command->authenticatedUserId = $signedIn->asUser()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = ValidIpsV4AndV6::listValidIps()[1];

        $response = $handler->handle($command);

        /** @var TokenRefreshed $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[1];

        $this->assertNotEquals(
            $command->withSessionToken,
            $storedEvent->refreshedSessionToken()
        );
        $this->assertEquals(
            [
                'refreshedSessionToken' => $storedEvent->refreshedSessionToken(),
            ],
            $response
        );
        Assert::assertEquals(
            [
                $storedEvent->eventId(),
                $signedIn->aggregateId(),
                $signedIn->aggregateVersion() + 1,
                $signedIn->eventId(),
                $signedIn->eventId(),
                $storedEvent->recordedOn(),
                $command->metadata,
                $command->withIp,
                $command->withSessionToken,
                $storedEvent->refreshedSessionToken()
            ],
            [
                $storedEvent->eventId(),
                $storedEvent->aggregateId(),
                $storedEvent->aggregateVersion(),
                $storedEvent->causationId(),
                $storedEvent->correlationId(),
                $storedEvent->recordedOn(),
                $storedEvent->metadata(),
                $storedEvent->withIp(),
                $storedEvent->withExistingSessionToken(),
                $storedEvent->refreshedSessionToken(),
            ]
        );


    }
    public function testAlreadySignedOut(): void
    {
        $this->expectException(AlreadySignedOut::class);

        $signedIn = SampleEvents::signedIn();
        $signedOut = SampleEvents::signedOut(
            $signedIn->aggregateId(),
            2,
            $signedIn->eventId(),
            $signedIn->eventId()
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($signedIn);
        $this->getInMemoryEventStore()->save($signedOut);
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
        $command->authenticatedUserId = $signedIn->asUser()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = ValidIpsV4AndV6::listValidIps()[1];

        $handler->handle($command);
    }

    public function testInvalidIp(): void
    {
        $this->expectException(InvalidIp::class);
        $signedIn = SampleEvents::signedIn();

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
        $command->authenticatedUserId = $signedIn->asUser()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = InvalidIpsV4AndV6::listInvalidIps()[1];

        $handler->handle($command);
    }

    public function testNoSessionFound(): void
    {
        $this->expectException(NoSessionFound::class);
        $signedIn = SampleEvents::signedIn();

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
        $command->authenticatedUserId = $signedIn->asUser()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated().'extra_characters';
        $command->withIp = InvalidIpsV4AndV6::listInvalidIps()[1];

        $handler->handle($command);
    }

    public function testSessionTokenDoesNotMatch(): void
    {
        $this->expectException(SessionTokenDoesNotMatch::class);
        $signedIn = SampleEvents::signedIn();

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
        $command->authenticatedUserId = $signedIn->asUser()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated().'extra_characters';
        $command->withIp = InvalidIpsV4AndV6::listInvalidIps()[1];

        $handler->handle($command);
    }

    public function testSessionUserDoesNotMatch(): void
    {
        $this->expectException(SessionUserDoesNotMatch::class);
        $signedIn = SampleEvents::signedIn();

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
        $command->authenticatedUserId = Id::createNew()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = InvalidIpsV4AndV6::listInvalidIps()[1];

        $handler->handle($command);
    }
}

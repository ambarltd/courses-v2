<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\CommandHandler;

use Galeas\Api\BoundedContext\Security\Session\Command\SignOut;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut\NoSessionFound;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut\SessionIdFromSessionToken;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut\SessionTokenDoesNotMatch;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut\SessionUserDoesNotMatch;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut\SignOutHandler;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip\ValidIpsV4AndV6;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidBCryptHashes;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Session\ValidDeviceLabels;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SignOutHandlerTest extends HandlerTestBase
{
    public function testHandle(): void
    {
        $signedIn = SampleEvents::signedIn();

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($signedIn);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new SignOutHandler(
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

        $command = new SignOut();
        $command->metadata = $this->mockMetadata();
        $command->authenticatedUserId = $signedIn->asUser()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = ValidIpsV4AndV6::listValidIps()[1];

        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[1];

        if (!($storedEvent instanceof SignedOut)) {
            throw new \Exception();
        }

        Assert::assertNotEquals($storedEvent->eventId(), $signedIn->eventId());

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
                $storedEvent->withSessionToken(),
            ]
        );
    }

    public function testNoSessionFound(): void
    {
        $this->expectException(NoSessionFound::class);
        $signedIn = SampleEvents::signedIn();


        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($signedIn);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new SignOutHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                SessionIdFromSessionToken::class,
                'sessionIdFromSessionToken',
                null
            )
        );

        $command = new SignOut();
        $command->metadata = $this->mockMetadata();
        $command->authenticatedUserId = $signedIn->asUser()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = ValidIpsV4AndV6::listValidIps()[1];

        $handler->handle($command);
    }

    public function testSessionTokenDoesNotMatch(): void
    {
        $this->expectException(SessionTokenDoesNotMatch::class);
        $signedIn = SampleEvents::signedIn();

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($signedIn);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new SignOutHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                SessionIdFromSessionToken::class,
                'sessionIdFromSessionToken',
                $signedIn->aggregateId()->id()
            )
        );

        $command = new SignOut();
        $command->metadata = $this->mockMetadata();
        $command->authenticatedUserId = $signedIn->asUser()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated().'extra_characters';
        $command->withIp = ValidIpsV4AndV6::listValidIps()[1];

        $handler->handle($command);
    }

    public function testSessionUserDoesNotMatch(): void
    {
        $this->expectException(SessionUserDoesNotMatch::class);
        $signedIn = SampleEvents::signedIn();

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($signedIn);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new SignOutHandler(
            $this->getInMemoryEventStore(),
            $this->mockForCommandHandlerWithReturnValue(
                SessionIdFromSessionToken::class,
                'sessionIdFromSessionToken',
                $signedIn->aggregateId()->id()
            )
        );

        $command = new SignOut();
        $command->metadata = $this->mockMetadata();
        $command->authenticatedUserId = $signedIn->asUser()->id().'extra_characters';
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = ValidIpsV4AndV6::listValidIps()[1];

        $handler->handle($command);
    }
}

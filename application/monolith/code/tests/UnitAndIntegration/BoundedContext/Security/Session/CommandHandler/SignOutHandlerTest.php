<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\CommandHandler;

use Galeas\Api\BoundedContext\Security\Session\Command\SignOut;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut\SessionIdFromSessionToken;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut\SignOutHandler;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\Common\Id\Id;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip\ValidIpsV4AndV6;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidBCryptHashes;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Session\ValidDeviceLabels;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;

class SignOutHandlerTest extends HandlerTestBase
{
    /**
     * @test
     *
     * @throws \Exception
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
        $command->authorizerId = $signedIn->authorizerId()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = ValidIpsV4AndV6::listValidIps()[1];

        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[1];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        if (!($storedEvent instanceof SignedOut)) {
            throw new \Exception();
        }

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
            $storedEvent->withSessionToken()
        );
        $this->assertEquals(
            $command->withIp,
            $storedEvent->withIp()
        );
        $this->assertEquals(
            $signedIn->aggregateId(),
            $storedEvent->aggregateId()
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut\NoSessionFound
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
        $command->authorizerId = $signedIn->authorizerId()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = ValidIpsV4AndV6::listValidIps()[1];

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut\SessionTokenDoesNotMatch
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
        $command->authorizerId = $signedIn->authorizerId()->id();
        $command->withSessionToken = $signedIn->sessionTokenCreated().'extra_characters';
        $command->withIp = ValidIpsV4AndV6::listValidIps()[1];

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut\SessionUserDoesNotMatch
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
        $command->authorizerId = $signedIn->authorizerId()->id().'extra_characters';
        $command->withSessionToken = $signedIn->sessionTokenCreated();
        $command->withIp = ValidIpsV4AndV6::listValidIps()[1];

        $handler->handle($command);
    }
}

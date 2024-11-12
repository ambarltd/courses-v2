<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection;

use Galeas\Api\BoundedContext\Security\Session\Projection\Session\SessionIdFromSessionToken;
use Galeas\Api\BoundedContext\Security\Session\Projection\Session\SessionProjector;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SessionTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testEvents()
    {
        $sessionIdFromSessionToken = $this->getContainer()
            ->get(SessionIdFromSessionToken::class)
        ;
        $sessionProjector = $this->getContainer()
            ->get(SessionProjector::class);

        $signedIn = SampleEvents::signedIn();
        $sessionProjector->project($signedIn);
        Assert::assertEquals(
            $signedIn->aggregateId()->id(),
            $sessionIdFromSessionToken->sessionIdFromSessionToken($signedIn->sessionTokenCreated())
        );

        $tokenRefreshed = SampleEvents::tokenRefreshed(
            $signedIn->aggregateId(),
            2,
            $signedIn->eventId(),
            $signedIn->eventId(),
        );
        $sessionProjector->project($tokenRefreshed);
        Assert::assertEquals(
            null,
            $sessionIdFromSessionToken->sessionIdFromSessionToken($signedIn->sessionTokenCreated())
        );
        Assert::assertEquals(
            $signedIn->aggregateId()->id(),
            $sessionIdFromSessionToken->sessionIdFromSessionToken($tokenRefreshed->refreshedSessionToken())
        );
    }
}
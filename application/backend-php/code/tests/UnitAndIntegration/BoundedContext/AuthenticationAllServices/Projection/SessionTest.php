<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\AuthenticationAllServices\Projection;

use Galeas\Api\BoundedContext\AuthenticationForAllContexts\Projection\Session\AuthenticatedUserIdFromSignedInSessionToken;
use Galeas\Api\BoundedContext\AuthenticationForAllContexts\Projection\Session\SessionProjector;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SessionTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testEvents(): void
    {
        $sessionProjector = $this->getContainer()
            ->get(SessionProjector::class)
        ;

        /** @var AuthenticatedUserIdFromSignedInSessionToken $authenticatedUserIdFromSignedInSessionToken */
        $authenticatedUserIdFromSignedInSessionToken = $this->getContainer()
            ->get(AuthenticatedUserIdFromSignedInSessionToken::class)
        ;

        $signedIn = SampleEvents::signedIn();
        $sessionProjector->projectIdempotently('test', $signedIn);
        Assert::assertEquals(
            null,
            $authenticatedUserIdFromSignedInSessionToken->authenticatedUserIdFromSignedInSessionToken($signedIn->sessionTokenCreated(), $signedIn->recordedOn()->modify('+1 millisecond'))
        );
        Assert::assertEquals(
            $signedIn->asUser()->id(),
            $authenticatedUserIdFromSignedInSessionToken->authenticatedUserIdFromSignedInSessionToken($signedIn->sessionTokenCreated(), $signedIn->recordedOn()->modify('-1 millisecond'))
        );

        $tokenRefreshed = SampleEvents::tokenRefreshed(
            $signedIn->aggregateId(),
            2,
            $signedIn->eventId(),
            $signedIn->eventId(),
        );
        $sessionProjector->projectIdempotently('test', $tokenRefreshed);
        Assert::assertEquals(
            null,
            $authenticatedUserIdFromSignedInSessionToken->authenticatedUserIdFromSignedInSessionToken($signedIn->sessionTokenCreated(), $signedIn->recordedOn()->modify('-1 millisecond'))
        );
        Assert::assertEquals(
            null,
            $authenticatedUserIdFromSignedInSessionToken->authenticatedUserIdFromSignedInSessionToken($tokenRefreshed->refreshedSessionToken(), $tokenRefreshed->recordedOn()->modify('+1 millisecond'))
        );
        Assert::assertEquals(
            $signedIn->asUser()->id(),
            $authenticatedUserIdFromSignedInSessionToken->authenticatedUserIdFromSignedInSessionToken($tokenRefreshed->refreshedSessionToken(), $tokenRefreshed->recordedOn()->modify('-1 millisecond'))
        );

        $signedOut = SampleEvents::signedOut(
            $signedIn->aggregateId(),
            3,
            $tokenRefreshed->eventId(),
            $signedIn->eventId(),
        );
        $sessionProjector->projectIdempotently('test', $signedOut);
        Assert::assertEquals(
            null,
            $authenticatedUserIdFromSignedInSessionToken->authenticatedUserIdFromSignedInSessionToken($tokenRefreshed->refreshedSessionToken(), $tokenRefreshed->recordedOn()->modify('-1 millisecond'))
        );
    }
}

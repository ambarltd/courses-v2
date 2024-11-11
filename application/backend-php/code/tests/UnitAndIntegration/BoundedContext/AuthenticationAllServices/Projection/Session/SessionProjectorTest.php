<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\AuthenticationForAllContexts\Projection\Session;

use Galeas\Api\BoundedContext\AuthenticationForAllContexts\Projection\Session\Session;
use Galeas\Api\BoundedContext\AuthenticationForAllContexts\Projection\Session\SessionProjector;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SessionProjectorTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testProcessSignedIn(): void
    {
        $sessionProjector = $this->getContainer()
            ->get(SessionProjector::class)
        ;

        $signedIn = SampleEvents::signedIn();
        $sessionProjector->project($signedIn);
        Assert::assertEquals(
            [
                Session::fromProperties(
                    $signedIn->aggregateId()->id(),
                    $signedIn->asUser()->id(),
                    $signedIn->sessionTokenCreated(),
                    false,
                    $signedIn->recordedOn()
                ),
            ],
            $this->findAllSessions()
        );

        $sessionProjector->project($signedIn);
        Assert::assertEquals(
            [
                Session::fromProperties(
                    $signedIn->aggregateId()->id(),
                    $signedIn->asUser()->id(),
                    $signedIn->sessionTokenCreated(),
                    false,
                    $signedIn->recordedOn()
                ),
            ],
            $this->findAllSessions()
        );
    }

    public function testProcessSignedInThenTokenRefreshed(): void
    {
        $sessionProjector = $this->getContainer()
            ->get(SessionProjector::class)
        ;

        $signedIn = SampleEvents::signedIn();
        $sessionProjector->project($signedIn);
        Assert::assertEquals(
            [
                Session::fromProperties(
                    $signedIn->aggregateId()->id(),
                    $signedIn->asUser()->id(),
                    $signedIn->sessionTokenCreated(),
                    false,
                    $signedIn->recordedOn()
                ),
            ],
            $this->findAllSessions()
        );

        $tokenRefreshed = SampleEvents::tokenRefreshed(
            $signedIn->aggregateId(),
            153,
            $signedIn->eventId(),
            $signedIn->eventId(),
        );
        $sessionProjector->project($tokenRefreshed);
        Assert::assertEquals(
            [
                Session::fromProperties(
                    $signedIn->aggregateId()->id(),
                    $signedIn->asUser()->id(),
                    $tokenRefreshed->refreshedSessionToken(),
                    false,
                    $tokenRefreshed->recordedOn()
                ),
            ],
            $this->findAllSessions()
        );

        $sessionProjector->project($tokenRefreshed);
        Assert::assertEquals(
            [
                Session::fromProperties(
                    $signedIn->aggregateId()->id(),
                    $signedIn->asUser()->id(),
                    $tokenRefreshed->refreshedSessionToken(),
                    false,
                    $tokenRefreshed->recordedOn()
                ),
            ],
            $this->findAllSessions()
        );
    }

    public function testProcessSignedInThenSignedOut(): void
    {
        $sessionProjector = $this->getContainer()
            ->get(SessionProjector::class)
        ;

        $signedIn = SampleEvents::signedIn();
        $sessionProjector->project($signedIn);
        Assert::assertEquals(
            [
                Session::fromProperties(
                    $signedIn->aggregateId()->id(),
                    $signedIn->asUser()->id(),
                    $signedIn->sessionTokenCreated(),
                    false,
                    $signedIn->recordedOn()
                ),
            ],
            $this->findAllSessions()
        );

        $signedOut = SampleEvents::signedOut(
            $signedIn->aggregateId(),
            153,
            $signedIn->eventId(),
            $signedIn->eventId(),
        );
        $sessionProjector->project($signedOut);
        Assert::assertEquals(
            [
                Session::fromProperties(
                    $signedIn->aggregateId()->id(),
                    $signedIn->asUser()->id(),
                    $signedIn->sessionTokenCreated(),
                    true,
                    $signedIn->recordedOn()
                ),
            ],
            $this->findAllSessions()
        );

        $sessionProjector->project($signedOut);
        Assert::assertEquals(
            [
                Session::fromProperties(
                    $signedIn->aggregateId()->id(),
                    $signedIn->asUser()->id(),
                    $signedIn->sessionTokenCreated(),
                    true,
                    $signedIn->recordedOn()
                ),
            ],
            $this->findAllSessions()
        );
    }

    private function findAllSessions(): array
    {
        return array_values(
            $this->getProjectionDocumentManager()
                ->createQueryBuilder(Session::class)
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }
}

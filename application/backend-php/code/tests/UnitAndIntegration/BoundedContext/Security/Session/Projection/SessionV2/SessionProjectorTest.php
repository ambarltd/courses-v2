<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\SessionV2;

use Galeas\Api\BoundedContext\Security\Session\Projection\SessionV2\Session;
use Galeas\Api\BoundedContext\Security\Session\Projection\SessionV2\SessionProjector;
use Galeas\Api\Common\Event\Event;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ProjectionAndReactionIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SessionProjectorTest extends ProjectionAndReactionIntegrationTest
{
    public function testProcessSignedIn(): void
    {
        $sessionProjector = $this->getContainer()
            ->get(SessionProjector::class)
        ;

        $signedIn = SampleEvents::signedIn();
        $this->projectEventAndClearDocumentManager($sessionProjector, $signedIn);
        Assert::assertEquals(
            [
                Session::fromProperties(
                    $signedIn->aggregateId()->id(),
                    $signedIn->sessionTokenCreated(),
                ),
            ],
            $this->findAllSessions()
        );

        $this->projectEventAndClearDocumentManager($sessionProjector, $signedIn);
        Assert::assertEquals(
            [
                Session::fromProperties(
                    $signedIn->aggregateId()->id(),
                    $signedIn->sessionTokenCreated(),
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
        $this->projectEventAndClearDocumentManager($sessionProjector, $signedIn);
        Assert::assertEquals(
            [
                Session::fromProperties(
                    $signedIn->aggregateId()->id(),
                    $signedIn->sessionTokenCreated(),
                ),
            ],
            $this->findAllSessions()
        );

        $tokenRefreshed = SampleEvents::tokenRefreshed(
            $signedIn->aggregateId(),
            $signedIn->aggregateVersion() + 1,
            $signedIn->eventId(),
            $signedIn->eventId()
        );
        $this->projectEventAndClearDocumentManager($sessionProjector, $tokenRefreshed);
        Assert::assertEquals(
            [
                Session::fromProperties(
                    $signedIn->aggregateId()->id(),
                    $tokenRefreshed->refreshedSessionToken(),
                ),
            ],
            $this->findAllSessions()
        );
    }

    private function projectEventAndClearDocumentManager(SessionProjector $sessionProjector, Event $event): void
    {
        $this->getProjectionDocumentManager()->clear();
        $sessionProjector->project($event);
        $this->getProjectionDocumentManager()->flush();
        $this->getProjectionDocumentManager()->clear();
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

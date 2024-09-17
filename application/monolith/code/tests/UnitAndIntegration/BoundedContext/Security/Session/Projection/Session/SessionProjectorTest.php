<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\Session;

use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\BoundedContext\Security\Session\Projection\Session\Session;
use Galeas\Api\BoundedContext\Security\Session\Projection\Session\SessionProjector;
use Galeas\Api\Common\Id\Id;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SessionProjectorTest extends KernelTestBase
{
    public function testProcessSignedIn(): void
    {
        $SessionProjector = $this->getContainer()
            ->get(SessionProjector::class);

        $signedIn = SampleEvents::signedIn();

        $SessionProjector->project($signedIn);
        // This make sure mongo is restoring DateTimeImmutable correctly.
        // The Session object with DateTimeImmutable gets recreated and rehydrated.
        // It's not necessary in every test, but having it here makes sure that
        // dates are being persisted and restored correctly with Mongo through the whole project.
        $this->getProjectionDocumentManager()->clear();

        $allSessions = $this->findAllSessions();
        $this->assertCount(
            1,
            $allSessions
        );
        $sessions = $this->findSessionsById(
            $signedIn->aggregateId()->id()
        );
        $this->assertCount(
            1,
            $sessions
        );
        $this->assertEquals(
            $signedIn->aggregateId()->id(),
            $sessions[0]->getSessionId()
        );
        $this->assertEquals(
            $signedIn->asUser()->id(),
            $sessions[0]->getUserId()
        );
        $this->assertEquals(
            $signedIn->sessionTokenCreated(),
            $sessions[0]->getSessionToken()
        );
        $this->assertEquals(
            false,
            $sessions[0]->isSignedOut()
        );
        $this->assertEquals(
            $signedIn->recordedOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );

        // test idempotency
        $SessionProjector->project($signedIn);

        $allSessions = $this->findAllSessions();
        $this->assertCount(
            1,
            $allSessions
        );
        $sessions = $this->findSessionsById(
            $signedIn->aggregateId()->id()
        );
        $this->assertCount(
            1,
            $sessions
        );
        $this->assertEquals(
            $signedIn->aggregateId()->id(),
            $sessions[0]->getSessionId()
        );
        $this->assertEquals(
            $signedIn->asUser()->id(),
            $sessions[0]->getUserId()
        );
        $this->assertEquals(
            $signedIn->sessionTokenCreated(),
            $sessions[0]->getSessionToken()
        );
        $this->assertEquals(
            false,
            $sessions[0]->isSignedOut()
        );
        $this->assertEquals(
            $signedIn->recordedOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );
    }

    public function testProcessTokenRefreshed(): void
    {
        $SessionProjector = $this->getContainer()
            ->get(SessionProjector::class);

        $tokenRefreshed = SampleEvents::tokenRefreshed(
            Id::createNew(),
            153,
            Id::createNew(),
            Id::createNew()
        );
        $SessionProjector->project($tokenRefreshed);

        $allSessions = $this->findAllSessions();
        $this->assertCount(
            1,
            $allSessions
        );
        $sessions = $this->findSessionsById(
            $tokenRefreshed->aggregateId()->id()
        );
        $this->assertCount(
            1,
            $sessions
        );
        $this->assertEquals(
            $tokenRefreshed->aggregateId()->id(),
            $sessions[0]->getSessionId()
        );
        $this->assertEquals(
            null, // asUser is defined in SignedIn, which wasn't processed in this case
            $sessions[0]->getUserId()
        );
        $this->assertEquals(
            $tokenRefreshed->refreshedSessionToken(),
            $sessions[0]->getSessionToken()
        );
        $this->assertEquals(
            false,
            $sessions[0]->isSignedOut()
        );
        $this->assertEquals(
            $tokenRefreshed->recordedOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );

        // test idempotency
        $SessionProjector->project($tokenRefreshed);

        $allSessions = $this->findAllSessions();
        $this->assertCount(
            1,
            $allSessions
        );
        $sessions = $this->findSessionsById(
            $tokenRefreshed->aggregateId()->id()
        );
        $this->assertCount(
            1,
            $sessions
        );
        $this->assertEquals(
            $tokenRefreshed->aggregateId()->id(),
            $sessions[0]->getSessionId()
        );
        $this->assertEquals(
            null, // asUser is defined in SignedIn, which wasn't processed in this case
            $sessions[0]->getUserId()
        );
        $this->assertEquals(
            $tokenRefreshed->refreshedSessionToken(),
            $sessions[0]->getSessionToken()
        );
        $this->assertEquals(
            false,
            $sessions[0]->isSignedOut()
        );
        $this->assertEquals(
            $tokenRefreshed->recordedOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );
    }

    public function testProcessSignedInThenTokenRefreshed(): void
    {
        $SessionProjector = $this->getContainer()
            ->get(SessionProjector::class);

        $signedIn = SampleEvents::signedIn();
        $tokenRefreshed = SampleEvents::tokenRefreshed(
            $signedIn->aggregateId(),
            2,
            $signedIn->eventId(),
            $signedIn->eventId(),
        );

        $SessionProjector->project($signedIn);
        $SessionProjector->project($tokenRefreshed);

        $allSessions = $this->findAllSessions();
        $this->assertCount(
            1,
            $allSessions
        );
        $sessions = $this->findSessionsById(
            $signedIn->aggregateId()->id()
        );
        $this->assertCount(
            1,
            $sessions
        );
        $this->assertEquals(
            $signedIn->aggregateId()->id(),
            $sessions[0]->getSessionId()
        );
        $this->assertEquals(
            $signedIn->asUser()->id(),
            $sessions[0]->getUserId()
        );
        $this->assertEquals(
            $tokenRefreshed->refreshedSessionToken(),
            $sessions[0]->getSessionToken()
        );
        $this->assertEquals(
            false,
            $sessions[0]->isSignedOut()
        );
        $this->assertEquals(
            $tokenRefreshed->recordedOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );

        // test idempotency
        $SessionProjector->project($tokenRefreshed);

        $allSessions = $this->findAllSessions();
        $this->assertCount(
            1,
            $allSessions
        );
        $sessions = $this->findSessionsById(
            $signedIn->aggregateId()->id()
        );
        $this->assertCount(
            1,
            $sessions
        );
        $this->assertEquals(
            $signedIn->aggregateId()->id(),
            $sessions[0]->getSessionId()
        );
        $this->assertEquals(
            $signedIn->asUser()->id(),
            $sessions[0]->getUserId()
        );
        $this->assertEquals(
            $tokenRefreshed->refreshedSessionToken(),
            $sessions[0]->getSessionToken()
        );
        $this->assertEquals(
            false,
            $sessions[0]->isSignedOut()
        );
        $this->assertEquals(
            $tokenRefreshed->recordedOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );
    }

    public function testProcessSignedOut(): void
    {
        $SessionProjector = $this->getContainer()
            ->get(SessionProjector::class);

        $signedOut = SampleEvents::signedOut(
            Id::createNew(),
            2,
            Id::createNew(),
            Id::createNew(),
        );
        $SessionProjector->project($signedOut);

        $allSessions = $this->findAllSessions();
        $this->assertCount(
            1,
            $allSessions
        );
        $sessions = $this->findSessionsById(
            $signedOut->aggregateId()->id()
        );
        $this->assertCount(
            1,
            $sessions
        );
        $this->assertEquals(
            $signedOut->aggregateId()->id(),
            $sessions[0]->getSessionId()
        );
        $this->assertEquals(
            null, // asUser is defined in SignedIn, which wasn't processed in this case
            $sessions[0]->getUserId()
        );
        $this->assertEquals(
            $signedOut->withSessionToken(),
            $sessions[0]->getSessionToken()
        );
        $this->assertEquals(
            true,
            $sessions[0]->isSignedOut()
        );
        $this->assertEquals(
            $signedOut->recordedOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );

        // test idempotency
        $SessionProjector->project($signedOut);

        $allSessions = $this->findAllSessions();
        $this->assertCount(
            1,
            $allSessions
        );
        $sessions = $this->findSessionsById(
            $signedOut->aggregateId()->id()
        );
        $this->assertCount(
            1,
            $sessions
        );
        $this->assertEquals(
            $signedOut->aggregateId()->id(),
            $sessions[0]->getSessionId()
        );
        $this->assertEquals(
            null, // asUser is defined in SignedIn, which wasn't processed in this case
            $sessions[0]->getUserId()
        );
        $this->assertEquals(
            $signedOut->withSessionToken(),
            $sessions[0]->getSessionToken()
        );
        $this->assertEquals(
            true,
            $sessions[0]->isSignedOut()
        );
        $this->assertEquals(
            $signedOut->recordedOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );
    }

    public function testProcessSignedInThenSignedOut(): void
    {
        /** @var SessionProjector $SessionProjector */
        $SessionProjector = $this->getContainer()
            ->get(SessionProjector::class);

        $signedIn = SampleEvents::signedIn();
        $signedOut = SampleEvents::signedOut(
            $signedIn->aggregateId(),
            2,
            $signedIn->eventId(),
            $signedIn->eventId(),
        );

        $SessionProjector->project($signedIn);
        $SessionProjector->project($signedOut);

        $allSessions = $this->findAllSessions();
        $this->assertCount(
            1,
            $allSessions
        );
        $sessions = $this->findSessionsById(
            $signedIn->aggregateId()->id()
        );
        $this->assertCount(
            1,
            $sessions
        );
        $this->assertEquals(
            $signedIn->aggregateId()->id(),
            $sessions[0]->getSessionId()
        );
        $this->assertEquals(
            $signedIn->asUser()->id(),
            $sessions[0]->getUserId()
        );
        $this->assertEquals(
            $signedOut->withSessionToken(),
            $sessions[0]->getSessionToken()
        );
        $this->assertEquals(
            true,
            $sessions[0]->isSignedOut()
        );
        $this->assertEquals(
            $signedIn->recordedOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );

        // test idempotency
        $SessionProjector->project($signedOut);

        $allSessions = $this->findAllSessions();
        $this->assertCount(
            1,
            $allSessions
        );
        $sessions = $this->findSessionsById(
            $signedIn->aggregateId()->id()
        );
        $this->assertCount(
            1,
            $sessions
        );
        $this->assertEquals(
            $signedIn->aggregateId()->id(),
            $sessions[0]->getSessionId()
        );
        $this->assertEquals(
            $signedIn->asUser()->id(),
            $sessions[0]->getUserId()
        );
        $this->assertEquals(
            $signedOut->withSessionToken(),
            $sessions[0]->getSessionToken()
        );
        $this->assertEquals(
            true,
            $sessions[0]->isSignedOut()
        );
        $this->assertEquals(
            $signedIn->recordedOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );
    }

    private function findSessionsById(string $sessionId): array
    {
        return array_values(
            $this->getProjectionDocumentManager()
                ->createQueryBuilder(Session::class)
                ->field('id')->equals($sessionId)
                ->getQuery()
                ->execute()
                ->toArray()
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

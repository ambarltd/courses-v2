<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\Session;

use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\BoundedContext\Security\Session\Projection\Session\Session;
use Galeas\Api\BoundedContext\Security\Session\Projection\Session\SessionProcessor;
use Galeas\Api\Common\Id\Id;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class SessionProcessorTest extends KernelTestBase
{
    /**
     * @test
     */
    public function testProcessSignedIn(): void
    {
        $sessionProcessor = $this->getContainer()
            ->get(SessionProcessor::class);

        $signedIn = SignedIn::fromProperties(
            [],
            Id::createNew(),
            'username_123',
            'email@example.com',
            'hashed_password',
            'byDeviceLabel',
            '127.128.129.130'
        );

        $sessionProcessor->process($signedIn);
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
            $signedIn->eventOccurredOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );

        // test idempotency
        $sessionProcessor->process($signedIn);

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
            $signedIn->eventOccurredOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );
    }

    /**
     * @test
     */
    public function testProcessTokenRefreshed(): void
    {
        $sessionProcessor = $this->getContainer()
            ->get(SessionProcessor::class);

        $sessionId = Id::createNew();
        $asUser = Id::createNew();
        $existingSessionToken = 'existing_session_token';

        $tokenRefreshed = TokenRefreshed::fromProperties(
            $sessionId,
            $asUser,
            [],
            '189.189.189.189',
            $existingSessionToken
        );

        $sessionProcessor->process($tokenRefreshed);

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
            $tokenRefreshed->eventOccurredOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );

        // test idempotency
        $sessionProcessor->process($tokenRefreshed);

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
            $tokenRefreshed->eventOccurredOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );
    }

    /**
     * @test
     */
    public function testProcessSignedInThenTokenRefreshed(): void
    {
        $sessionProcessor = $this->getContainer()
            ->get(SessionProcessor::class);

        $signedIn = SignedIn::fromProperties(
            [],
            Id::createNew(),
            'username_123',
            'email@example.com',
            'hashed_password',
            'byDeviceLabel',
            '127.128.129.130'
        );
        $tokenRefreshed = TokenRefreshed::fromProperties(
            $signedIn->aggregateId(),
            $signedIn->asUser(),
            [],
            '189.189.189.189',
            $signedIn->sessionTokenCreated()
        );

        $sessionProcessor->process($signedIn);
        $sessionProcessor->process($tokenRefreshed);

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
            $tokenRefreshed->eventOccurredOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );

        // test idempotency
        $sessionProcessor->process($tokenRefreshed);

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
            $tokenRefreshed->eventOccurredOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );
    }

    /**
     * @test
     */
    public function testProcessSignedOut(): void
    {
        $sessionProcessor = $this->getContainer()
            ->get(SessionProcessor::class);

        $sessionId = Id::createNew();
        $asUser = Id::createNew();
        $existingSessionToken = 'existing_session_token';

        $signedOut = SignedOut::fromProperties(
            $sessionId,
            $asUser,
            [],
            '189.189.189.189',
            $existingSessionToken
        );

        $sessionProcessor->process($signedOut);

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
            $signedOut->eventOccurredOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );

        // test idempotency
        $sessionProcessor->process($signedOut);

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
            $signedOut->eventOccurredOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );
    }

    /**
     * @test
     */
    public function testProcessSignedInThenSignedOut(): void
    {
        $sessionProcessor = $this->getContainer()
            ->get(SessionProcessor::class);

        $signedIn = SignedIn::fromProperties(
            [],
            Id::createNew(),
            'username_123',
            'email@example.com',
            'hashed_password',
            'byDeviceLabel',
            '127.128.129.130'
        );
        $signedOut = SignedOut::fromProperties(
            $signedIn->aggregateId(),
            $signedIn->asUser(),
            [],
            '189.189.189.189',
            $signedIn->sessionTokenCreated()
        );

        $sessionProcessor->process($signedIn);
        $sessionProcessor->process($signedOut);

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
            true,
            $sessions[0]->isSignedOut()
        );
        $this->assertEquals(
            $signedIn->eventOccurredOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );

        // test idempotency
        $sessionProcessor->process($signedOut);

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
            true,
            $sessions[0]->isSignedOut()
        );
        $this->assertEquals(
            $signedIn->eventOccurredOn(),
            $sessions[0]->getTokenLastRefreshedAt()
        );
    }

    /**
     * @return Session[]
     */
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

    /**
     * @return Session[]
     */
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

<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\Session;

use Galeas\Api\BoundedContext\Security\Session\Projection\Session\Session;
use Galeas\Api\BoundedContext\Security\Session\Projection\Session\SessionIdFromSessionToken;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;

class SessionIdFromSessionTokenTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testSessionIdFromSessionToken(): void
    {
        $sessionIdFromSessionToken = $this->getContainer()
            ->get(SessionIdFromSessionToken::class)
        ;

        Assert::assertEquals(
            null,
            $sessionIdFromSessionToken->sessionIdFromSessionToken('session_token_123')
        );
        Assert::assertEquals(
            null,
            $sessionIdFromSessionToken->sessionIdFromSessionToken('session_token_1234')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                Session::fromProperties(
                    'session_id_123',
                    'session_token_123',
                )
            )
        ;
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            'session_id_123',
            $sessionIdFromSessionToken->sessionIdFromSessionToken('session_token_123')
        );
        Assert::assertEquals(
            null,
            $sessionIdFromSessionToken->sessionIdFromSessionToken('session_token_1234')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                Session::fromProperties(
                    'session_id_1234',
                    'session_token_1234',
                )
            )
        ;
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            'session_id_123',
            $sessionIdFromSessionToken->sessionIdFromSessionToken('session_token_123')
        );
        Assert::assertEquals(
            'session_id_1234',
            $sessionIdFromSessionToken->sessionIdFromSessionToken('session_token_1234')
        );
    }
}

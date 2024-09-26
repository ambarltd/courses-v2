<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\SessionV2;

use Galeas\Api\BoundedContext\Security\Session\Projection\SessionV2\Session;
use Galeas\Api\BoundedContext\Security\Session\Projection\SessionV2\SessionIdFromSessionToken;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ProjectionAndReactionIntegrationTest;

class SessionIdFromSessionTokenTest extends ProjectionAndReactionIntegrationTest
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

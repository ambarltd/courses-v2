<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\AuthenticationForAllContexts\Projection\Session;

use Galeas\Api\BoundedContext\AuthenticationForAllContexts\Projection\Session\Session;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;

class SessionTest extends UnitTest
{
    public function testCreate(): void
    {
        $lastRefreshedAt = new \DateTimeImmutable();
        $session = Session::fromProperties(
            'session_id',
            'user_id',
            'session_token',
            false,
            $lastRefreshedAt
        );

        Assert::assertEquals(
            'session_id',
            $session->getSessionId()
        );
        Assert::assertEquals(
            'user_id',
            $session->getUserId()
        );
        Assert::assertEquals(
            'session_token',
            $session->getSessionToken()
        );
        Assert::assertEquals(
            false,
            $session->isSignedOut()
        );
        Assert::assertEquals(
            $lastRefreshedAt,
            $session->getTokenLastRefreshedAt()
        );
    }

    public function testChangeProperties(): void
    {
        $lastRefreshedAt = new \DateTimeImmutable();
        $session = Session::fromProperties(
            'session_id',
            'user_id',
            'session_token',
            false,
            $lastRefreshedAt
        );
        $changedLastRefreshedAt = new \DateTimeImmutable('+ 5 minutes');
        $session->changeProperties(
            'changed_session_token',
            true,
            $changedLastRefreshedAt
        );

        Assert::assertEquals(
            'session_id',
            $session->getSessionId()
        );
        Assert::assertEquals(
            'changed_session_token',
            $session->getSessionToken()
        );
        Assert::assertEquals(
            true,
            $session->isSignedOut()
        );
        Assert::assertEquals(
            $changedLastRefreshedAt,
            $session->getTokenLastRefreshedAt()
        );

        $session->changeProperties(
            'changed_session_token',
            false,
            $changedLastRefreshedAt
        );

        Assert::assertEquals(
            false,
            $session->isSignedOut()
        );
    }
}

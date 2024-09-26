<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\SessionV2;

use Galeas\Api\BoundedContext\Security\Session\Projection\SessionV2\Session;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;

class SessionTest extends UnitTest
{
    public function testCreate(): void
    {
        $session = Session::fromProperties(
            'session_id',
            'session_token',
        );

        Assert::assertEquals(
            'session_id',
            $session->getSessionId()
        );
        Assert::assertEquals(
            'session_token',
            $session->getSessionToken()
        );
    }

    public function testChangeProperties(): void
    {
        $session = Session::fromProperties(
            'session_id',
            'session_token',
        );
        $session->changeProperties(
            'changed_session_token',
        );

        Assert::assertEquals(
            'session_id',
            $session->getSessionId()
        );
        Assert::assertEquals(
            'changed_session_token',
            $session->getSessionToken()
        );
    }
}

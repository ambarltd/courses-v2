<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\ValueObject;

use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionIsSignedOut;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SessionIsSignedOutTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $withSessionToken = 'with_session_token';
        $withIp = '127.0.0.1';

        $sessionDetails = SessionIsSignedOut::fromProperties(
            $withSessionToken,
            $withIp
        );

        Assert::assertEquals($withSessionToken, $sessionDetails->withSessionToken());
        Assert::assertEquals($withIp, $sessionDetails->withIp());
    }
}

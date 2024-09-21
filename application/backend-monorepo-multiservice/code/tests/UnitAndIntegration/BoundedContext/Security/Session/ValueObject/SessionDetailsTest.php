<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\ValueObject;

use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SessionDetailsTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $asUser = Id::createNew();
        $withUsername = 'test_username';
        $withEmail = 'test_email';
        $withHashedPassword = 'test_hashed_password';
        $byDeviceLabel = 'by_device_label';
        $withIp = '127.0.0.1';
        $withSessionToken = 'with_session_token';

        $sessionDetails = SessionDetails::fromProperties(
            $asUser,
            $withUsername,
            $withEmail,
            $withHashedPassword,
            $byDeviceLabel,
            $withIp,
            $withSessionToken
        );

        Assert::assertEquals($asUser, $sessionDetails->asUser());
        Assert::assertEquals($withUsername, $sessionDetails->withUsername());
        Assert::assertEquals($withEmail, $sessionDetails->withEmail());
        Assert::assertEquals($withHashedPassword, $sessionDetails->withHashedPassword());
        Assert::assertEquals($byDeviceLabel, $sessionDetails->byDeviceLabel());
        Assert::assertEquals($withIp, $sessionDetails->withIp());
        Assert::assertEquals($withSessionToken, $sessionDetails->withSessionToken());
    }
}

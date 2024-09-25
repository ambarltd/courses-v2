<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\UserDetails;

use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\UserDetails;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\VerifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\VerifiedEmailButRequestedNewEmail;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;

class UserDetailsTest extends UnitTest
{
    public function testUserDetails(): void
    {
        $userDetails = UserDetails::fromProperties(
            'userId',
            UnverifiedEmail::fromProperties('email1')
        );
        Assert::assertEquals('userId', $userDetails->getUserId());
        Assert::assertEquals(UnverifiedEmail::fromProperties('email1'), $userDetails->getPrimaryEmailStatus());

        $userDetails->changePrimaryEmailStatus(VerifiedEmail::fromProperties('email2'));
        Assert::assertEquals(VerifiedEmail::fromProperties('email2'), $userDetails->getPrimaryEmailStatus());

        $userDetails->changePrimaryEmailStatus(VerifiedEmailButRequestedNewEmail::fromProperties('email3', 'email4'));
        Assert::assertEquals(VerifiedEmailButRequestedNewEmail::fromProperties('email3', 'email4'), $userDetails->getPrimaryEmailStatus());
    }
}

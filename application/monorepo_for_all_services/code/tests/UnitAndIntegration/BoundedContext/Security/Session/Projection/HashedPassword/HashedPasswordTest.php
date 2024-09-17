<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\HashedPassword;

use Galeas\Api\BoundedContext\Security\Session\Projection\HashedPassword\HashedPassword;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class HashedPasswordTest extends UnitTestBase
{
    public function testHashedPassword(): void
    {
        $hashedPassword = HashedPassword::fromUserIdAndHashedPassword(
            'user_id',
            'hashed_password'
        );

        Assert::assertEquals('user_id', $hashedPassword->getUserId());
        Assert::assertEquals('hashed_password', $hashedPassword->getHashedPassword());

        $hashedPassword->changeHashedPassword('hashed_password_2');

        Assert::assertEquals('user_id', $hashedPassword->getUserId());
        Assert::assertEquals('hashed_password_2', $hashedPassword->getHashedPassword());
    }
}

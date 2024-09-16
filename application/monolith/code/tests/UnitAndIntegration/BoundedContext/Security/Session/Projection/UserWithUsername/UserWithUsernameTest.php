<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\UserWithUsername;

use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername\UserWithUsername;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class UserWithUsernameTest extends UnitTestBase
{
    public function testUserWithUsername(): void
    {
        $userWithUsername = UserWithUsername::fromProperties(
            'SomeUsername',
            'userId_01'
        );

        Assert::assertEquals(
            'someusername',
            $userWithUsername->getCanonicalUsername()
        );
        Assert::assertEquals(
            'userId_01',
            $userWithUsername->getUserId()
        );

        $userWithUsername->changeUsername('SomeUsername2');

        Assert::assertEquals(
            'someusername2',
            $userWithUsername->getCanonicalUsername()
        );
        Assert::assertEquals(
            'userId_01',
            $userWithUsername->getUserId()
        );
    }
}

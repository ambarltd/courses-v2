<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\UserWithUsername;

use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername\UserIdFromSignInUsername;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername\UserWithUsername;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class UserIdFromSignInUsernameTest extends KernelTestBase
{
    public function testUserIdFromUsernameTest(): void
    {
        $userIdFromUsername = $this->getContainer()
            ->get(UserIdFromSignInUsername::class);

        Assert::assertEquals(
            null,
            $userIdFromUsername->userIdFromSignInUsername('username_123')
        );
        Assert::assertEquals(
            null,
            $userIdFromUsername->userIdFromSignInUsername('username_1234')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                UserWithUsername::fromProperties(
                    'username_123',
                    'user_id_123'
                )
            );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            'user_id_123',
            $userIdFromUsername->userIdFromSignInUsername('username_123')
        );
        Assert::assertEquals(
            null,
            $userIdFromUsername->userIdFromSignInUsername('username_1234')
        );

        // test second user, and make sure it's done with the canonical username
        $this->getProjectionDocumentManager()
            ->persist(
                UserWithUsername::fromProperties(
                    'useRName_1234',
                    'user_id_1234'
                )
            );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            'user_id_123',
            $userIdFromUsername->userIdFromSignInUsername('username_123')
        );
        Assert::assertEquals(
            'user_id_1234',
            $userIdFromUsername->userIdFromSignInUsername('USername_1234')
        );
    }
}

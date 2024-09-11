<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ExistingUser;

use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ExistingUser\ExistingUser;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ExistingUserTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testExistingUser(): void
    {
        $existingUser = ExistingUser::fromUserId(
            'user_id_123'
        );

        Assert::assertEquals('user_id_123', $existingUser->getUserId());
    }
}

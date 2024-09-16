<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\TakenUsername;

use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\TakenUsername;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class TakenUsernameTest extends UnitTestBase
{
    public function testTakenUsername(): void
    {
        $takenUsername = TakenUsername::fromUserIdAndUsername(
            'user_id_test',
            'testUserNAme2'
        );

        Assert::assertEquals('user_id_test', $takenUsername->getUserId());
        Assert::assertEquals('testusername2', $takenUsername->getCanonicalUsername());
        Assert::assertSame($takenUsername, $takenUsername->changeUsername('testUsername44'));
        Assert::assertEquals('user_id_test', $takenUsername->getUserId());
        Assert::assertEquals('testusername44', $takenUsername->getCanonicalUsername());
    }
}

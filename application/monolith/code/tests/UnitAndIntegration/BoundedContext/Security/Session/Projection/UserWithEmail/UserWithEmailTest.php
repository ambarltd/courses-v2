<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\UserWithEmail;

use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\Unverified;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\UserWithEmail;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\Verified;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class UserWithEmailTest extends UnitTestBase
{
    public function testUserWithEmail(): void
    {
        $userWithEmail = UserWithEmail::fromUserIdAndEmails(
            'user_id_test',
            null,
            'reQuested@example.com',
            Unverified::setStatus()
        );
        Assert::assertEquals('user_id_test', $userWithEmail->getUserId());
        Assert::assertEquals(null, $userWithEmail->getCanonicalVerifiedEmail());
        Assert::assertEquals('requested@example.com', $userWithEmail->getCanonicalRequestedEmail());
        Assert::assertEquals(Unverified::setStatus(), $userWithEmail->getStatus());

        Assert::assertSame(
            $userWithEmail,
            $userWithEmail->changeEmails(
                'Af5@example.com',
                null,
                Verified::setStatus()
            )
        );
        Assert::assertEquals('user_id_test', $userWithEmail->getUserId());
        Assert::assertEquals('af5@example.com', $userWithEmail->getCanonicalVerifiedEmail());
        Assert::assertEquals(null, $userWithEmail->getCanonicalRequestedEmail());
        Assert::assertEquals(Verified::setStatus(), $userWithEmail->getStatus());
    }
}

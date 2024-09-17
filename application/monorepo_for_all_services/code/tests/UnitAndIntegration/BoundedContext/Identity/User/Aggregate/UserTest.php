<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Aggregate;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\AccountDetails;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\HashedPassword;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class UserTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $userId = Id::createNew();
        $unverifiedEmail = UnverifiedEmail::fromEmailAndVerificationCode(
            Email::fromEmail(
                'test@example.com'
            ),
            VerificationCode::fromVerificationCode(
                '123454'
            )
        );
        $hashedPassword = HashedPassword::fromHash(
            'abcdef'
        );
        $accountDetails = AccountDetails::fromDetails(
            'username_1',
            true
        );
        $user = User::fromProperties(
            $userId,
            55,
            $unverifiedEmail,
            $hashedPassword,
            $accountDetails
        );

        Assert::assertEquals($userId, $user->aggregateId());
        Assert::assertEquals(55, $user->aggregateVersion());
        Assert::assertEquals($unverifiedEmail, $user->primaryEmailStatus());
        Assert::assertEquals($hashedPassword, $user->hashedPassword());
        Assert::assertEquals($accountDetails, $user->accountDetails());
    }
}

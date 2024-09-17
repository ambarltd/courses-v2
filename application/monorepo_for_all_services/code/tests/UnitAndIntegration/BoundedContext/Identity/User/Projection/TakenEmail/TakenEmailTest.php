<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\TakenEmail;

use Galeas\Api\BoundedContext\Identity\User\Projection\TakenEmail\TakenEmail;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class TakenEmailTest extends UnitTestBase
{
    public function testTakenEmail(): void
    {
        $takenEmail = TakenEmail::fromUserIdAndEmails(
            'user_id_test',
            'emAil1@example.com',
            'emAil2@example.com'
        );

        Assert::assertEquals('user_id_test', $takenEmail->getUserId());
        Assert::assertEquals('email1@example.com', $takenEmail->getCanonicalVerifiedEmail());
        Assert::assertEquals('email2@example.com', $takenEmail->getCanonicalRequestedEmail());
        Assert::assertSame($takenEmail, $takenEmail->changeEmails('Email3@example.com', 'Email4@example.com'));
        Assert::assertEquals('user_id_test', $takenEmail->getUserId());
        Assert::assertEquals('email3@example.com', $takenEmail->getCanonicalVerifiedEmail());
        Assert::assertEquals('email4@example.com', $takenEmail->getCanonicalRequestedEmail());
        Assert::assertSame($takenEmail, $takenEmail->changeEmails(null, 'Email4@example.com'));
        Assert::assertEquals('user_id_test', $takenEmail->getUserId());
        Assert::assertEquals(null, $takenEmail->getCanonicalVerifiedEmail());
        Assert::assertEquals('email4@example.com', $takenEmail->getCanonicalRequestedEmail());
        Assert::assertSame($takenEmail, $takenEmail->changeEmails('Email3@example.com', null));
        Assert::assertEquals('user_id_test', $takenEmail->getUserId());
        Assert::assertEquals('email3@example.com', $takenEmail->getCanonicalVerifiedEmail());
        Assert::assertEquals(null, $takenEmail->getCanonicalRequestedEmail());
        Assert::assertSame($takenEmail, $takenEmail->changeEmails(null, null));
        Assert::assertEquals('user_id_test', $takenEmail->getUserId());
        Assert::assertEquals(null, $takenEmail->getCanonicalVerifiedEmail());
        Assert::assertEquals(null, $takenEmail->getCanonicalRequestedEmail());
    }
}

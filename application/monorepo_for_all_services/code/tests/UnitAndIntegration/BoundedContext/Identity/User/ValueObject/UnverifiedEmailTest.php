<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\ValueObject;

use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class UnverifiedEmailTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $email = Email::fromEmail('test@example.com');
        $verificationCode = VerificationCode::fromVerificationCode('abcdef1234');
        $unverifiedEmail = UnverifiedEmail::fromEmailAndVerificationCode(
            $email,
            $verificationCode
        );

        Assert::assertEquals($email, $unverifiedEmail->email());
        Assert::assertEquals($verificationCode, $unverifiedEmail->verificationCode());
    }
}

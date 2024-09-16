<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\ValueObject;

use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedButRequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class RequestedNewEmailTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $verifiedEmail = Email::fromEmail('test@example.com');
        $requestedEmail = Email::fromEmail('test2@example.com');
        $verificationCode = VerificationCode::fromVerificationCode('1234567890');
        $requestedNewEmail = VerifiedButRequestedNewEmail::fromEmailsAndVerificationCode(
            $verifiedEmail,
            $requestedEmail,
            $verificationCode
        );

        Assert::assertEquals($verifiedEmail, $requestedNewEmail->verifiedEmail());
        Assert::assertEquals($requestedEmail, $requestedNewEmail->requestedEmail());
        Assert::assertEquals($verificationCode, $requestedNewEmail->verificationCode());
    }
}

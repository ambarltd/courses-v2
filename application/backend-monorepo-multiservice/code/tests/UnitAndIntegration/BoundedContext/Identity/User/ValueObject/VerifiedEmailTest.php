<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\ValueObject;

use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class VerifiedEmailTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $email = Email::fromEmail('test@example.com');
        $verifiedEmail = VerifiedEmail::fromEmail($email);

        Assert::assertEquals($email, $verifiedEmail->email());
    }
}

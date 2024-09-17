<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\ValueObject;

use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class VerificationCodeTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $verificationCode = VerificationCode::fromVerificationCode('abchan1237123');
        Assert::assertEquals('abchan1237123', $verificationCode->verificationCode());
    }
}

<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\ValueObject;

use Galeas\Api\BoundedContext\Identity\User\ValueObject\HashedPassword;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class HashedPasswordTest extends UnitTestBase
{
    public function testValid(): void
    {
        $hashedPassword = HashedPassword::fromHash('0123456789abcdef');
        Assert::assertEquals('0123456789abcdef', $hashedPassword->hash());
    }
}

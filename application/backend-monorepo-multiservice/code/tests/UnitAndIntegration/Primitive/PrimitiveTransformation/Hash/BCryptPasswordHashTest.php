<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveTransformation\Hash;

use Galeas\Api\Primitive\PrimitiveTransformation\Hash\BCryptPasswordHash;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class BCryptPasswordHashTest extends UnitTestBase
{
    public function testHash(): void
    {
        $hash = BCryptPasswordHash::hash('HelloWorld22AHHH!', 11);
        if (null === $hash) {
            throw new \Exception('Could not get hash.');
        }
        Assert::assertTrue(
            password_verify('HelloWorld22AHHH!', $hash)
        );
        Assert::assertStringStartsWith(
            '$2y$11$',
            $hash
        );

        $secondHash = BCryptPasswordHash::hash('FooBar55123!', 9);
        if (null === $secondHash) {
            throw new \Exception('Could not get hash.');
        }
        Assert::assertTrue(
            password_verify('FooBar55123!', $secondHash)
        );
        Assert::assertStringStartsWith(
            '$2y$09$',
            $secondHash
        );
    }
}

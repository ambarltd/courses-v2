<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username;

use Galeas\Api\Primitive\PrimitiveValidation\Username\UsernameValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class UsernameValidatorTest extends UnitTestBase
{
    public function testValidUsernames(): void
    {
        foreach (ValidUsernames::listValidUsernames() as $username) {
            if (false === UsernameValidator::isValid($username)) {
                Assert::fail($username.' should be valid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testInvalidUsernames(): void
    {
        foreach (InvalidUsernames::listInvalidUsernames() as $username) {
            if (true === UsernameValidator::isValid($username)) {
                Assert::fail($username.' should be invalid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}

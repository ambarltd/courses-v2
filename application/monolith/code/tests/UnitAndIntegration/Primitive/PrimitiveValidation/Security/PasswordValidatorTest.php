<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security;

use Galeas\Api\Primitive\PrimitiveValidation\Security\PasswordValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class PasswordValidatorTest extends UnitTestBase
{
    public function testValid(): void
    {
        foreach (ValidPasswords::listValidPasswords() as $password) {
            if (false === PasswordValidator::isValid($password)) {
                Assert::fail($password.' should be valid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testInvalid(): void
    {
        foreach (InvalidPasswords::listInvalidPasswords() as $password) {
            if (true === PasswordValidator::isValid($password)) {
                Assert::fail($password.' should be invalid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}

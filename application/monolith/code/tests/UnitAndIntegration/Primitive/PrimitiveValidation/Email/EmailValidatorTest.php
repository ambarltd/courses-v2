<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email;

use Galeas\Api\Primitive\PrimitiveValidation\Email\EmailValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class EmailValidatorTest extends UnitTestBase
{
    public function testValidEmails(): void
    {
        foreach (ValidEmails::listValidEmails() as $email) {
            if (false === EmailValidator::isValid($email)) {
                Assert::fail($email.' should be valid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testInvalidEmails(): void
    {
        foreach (InvalidEmails::listInvalidEmails() as $email) {
            if (true === EmailValidator::isValid($email)) {
                Assert::fail($email.' should be valid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}

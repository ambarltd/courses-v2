<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email;

use Galeas\Api\Primitive\PrimitiveValidation\Email\EmailVerificationCodeValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class EmailVerificationCodeValidatorTest extends UnitTestBase
{
    public function testValidVerificationCodes(): void
    {
        foreach (ValidVerificationCodes::listValidVerificationCodes() as $code) {
            if (false === EmailVerificationCodeValidator::isValid($code)) {
                Assert::fail($code.' should be valid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testInvalidVerificationCodes(): void
    {
        foreach (InvalidVerificationCodes::listInvalidVerificationCodes() as $code) {
            if (true === EmailVerificationCodeValidator::isValid($code)) {
                Assert::fail($code.' should be invalid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}

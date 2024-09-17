<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Session;

use Galeas\Api\Primitive\PrimitiveValidation\Session\SessionTokenValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SessionTokenValidatorTest extends UnitTestBase
{
    public function testValidSessionTokens(): void
    {
        foreach (ValidSessionTokens::listValidSessionTokens() as $token) {
            if (false === SessionTokenValidator::isValid($token)) {
                Assert::fail($token.' should be valid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testInvalidSessionTokens(): void
    {
        foreach (InvalidSessionTokens::listInvalidSessionTokens() as $token) {
            if (true === SessionTokenValidator::isValid($token)) {
                Assert::fail($token.' should be invalid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}

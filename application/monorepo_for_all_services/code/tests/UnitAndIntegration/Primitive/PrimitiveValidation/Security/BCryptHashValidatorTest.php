<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security;

use Galeas\Api\Primitive\PrimitiveValidation\Security\BCryptHashValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class BCryptHashValidatorTest extends UnitTestBase
{
    public function testValid(): void
    {
        foreach (ValidBCryptHashes::listValidBCryptHashes() as $hash) {
            if (false === BCryptHashValidator::isValid($hash)) {
                Assert::fail($hash.' should be valid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testInvalid(): void
    {
        foreach (InvalidBCryptHashes::listInvalidBCryptHashes() as $hash) {
            if (true === BCryptHashValidator::isValid($hash)) {
                Assert::fail($hash.' should be invalid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}

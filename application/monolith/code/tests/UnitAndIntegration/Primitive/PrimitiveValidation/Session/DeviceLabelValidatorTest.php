<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Session;

use Galeas\Api\Primitive\PrimitiveValidation\Session\DeviceLabelValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class DeviceLabelValidatorTest extends UnitTestBase
{
    public function testValidDeviceLabels(): void
    {
        foreach (ValidDeviceLabels::listValidDeviceLabels() as $label) {
            if (false === DeviceLabelValidator::isValid($label)) {
                Assert::fail($label.' should be valid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testInvalidDeviceLabels(): void
    {
        foreach (InvalidDeviceLabels::listInvalidDeviceLabels() as $label) {
            if (true === DeviceLabelValidator::isValid($label)) {
                Assert::fail($label.' should be invalid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}

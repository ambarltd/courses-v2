<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Id;

use Galeas\Api\Primitive\PrimitiveValidation\Id\IdValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class IdValidatorTest extends UnitTestBase
{
    public function testValidIds(): void
    {
        foreach (ValidIds::listValidIds() as $id) {
            if (false === IdValidator::isValid($id)) {
                Assert::fail($id.' should be valid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testInvalidIds(): void
    {
        foreach (InvalidIds::listInvalidIds() as $id) {
            if (true === IdValidator::isValid($id)) {
                Assert::fail($id.' should be invalid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}

<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip;

use Galeas\Api\Primitive\PrimitiveValidation\Ip\IpV4AndV6Validator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class IpV4AndV6ValidatorTest extends UnitTestBase
{
    public function testValidIps(): void
    {
        foreach (ValidIpsV4AndV6::listValidIps() as $ip) {
            if (false === IpV4AndV6Validator::isValid($ip)) {
                Assert::fail($ip.' should be valid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testInvalidIps(): void
    {
        foreach (InvalidIpsV4AndV6::listInvalidIps() as $ip) {
            if (true === IpV4AndV6Validator::isValid($ip)) {
                Assert::fail($ip.' should be invalid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}

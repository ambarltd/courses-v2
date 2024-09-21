<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip;

use Galeas\Api\Primitive\PrimitiveValidation\Ip\PrivateAndReservedIpV4AndV6Validator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class PrivateAndReservedIpV4AndV6ValidatorTest extends UnitTestBase
{
    public function testValidIps(): void
    {
        foreach (ValidPrivateAndReservedIpsV4AndV6::listValidIps() as $ip) {
            if (false === PrivateAndReservedIpV4AndV6Validator::isValid($ip)) {
                Assert::fail($ip.' should be valid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testInvalidIps(): void
    {
        foreach (InvalidPrivateAndReservedIpsV4AndV6::listInvalidIps() as $ip) {
            if (true === PrivateAndReservedIpV4AndV6Validator::isValid($ip)) {
                Assert::fail($ip.' should be invalid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}

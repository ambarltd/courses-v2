<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveComparison\Email;

use Galeas\Api\Primitive\PrimitiveComparison\Email\AreEmailsEquivalent;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class AreEmailsEquivalentTest extends UnitTestBase
{
    public function testEquivalentEmails(): void
    {
        foreach (EquivalentEmails::validEmailPairsWhichAreTheSameAddress() as $pair) {
            if (false === AreEmailsEquivalent::areEmailsEquivalent($pair[0], $pair[1])) {
                Assert::fail(sprintf(
                    '%s and %s should be equivalent',
                    $pair[0],
                    $pair[1]
                ));
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    public function testNonEquivalentEmails(): void
    {
        foreach (NonEquivalentEmails::validEmailPairsWhichAreNotTheSameAddress() as $pair) {
            if (true === AreEmailsEquivalent::areEmailsEquivalent($pair[0], $pair[1])) {
                Assert::fail(sprintf(
                    '%s and %s should not be equivalent',
                    $pair[0],
                    $pair[1]
                ));
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}

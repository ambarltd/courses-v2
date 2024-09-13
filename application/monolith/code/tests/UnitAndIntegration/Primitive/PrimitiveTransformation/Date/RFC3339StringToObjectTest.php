<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveTransformation\Date;

use Galeas\Api\Primitive\PrimitiveTransformation\Date\RFC3339StringToObject;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class RFC3339StringToObjectTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testValidDateStrings(): void
    {
        foreach (ValidRFC3339Strings::listValidRFC3339Strings() as $dateString) {
            $date = RFC3339StringToObject::transform($dateString);

            if (false === ($date instanceof \DateTimeImmutable)) {
                Assert::fail($dateString.' was not converted to a date.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }

    /**
     * @test
     */
    public function testInvalidDateStrings(): void
    {
        foreach (InvalidRFC3339Strings::listInvalidRFC3339Strings() as $dateString) {
            $date = RFC3339StringToObject::transform($dateString);

            if ($date instanceof \DateTimeImmutable) {
                Assert::fail($dateString.' should be invalid.');
            }
        }

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}

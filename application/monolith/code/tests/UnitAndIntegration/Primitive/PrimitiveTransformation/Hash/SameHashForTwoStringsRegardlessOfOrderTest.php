<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveTransformation\Hash;

use Galeas\Api\Primitive\PrimitiveTransformation\Hash\SameHashForTwoStringsRegardlessOfOrder;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SameHashForTwoStringsRegardlessOfOrderTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testHash(): void
    {
        $firstString = sha1('0000');
        $secondString = sha1('1111');

        Assert::assertEquals(
            hash('sha512',
                $secondString.$firstString
            ),
            SameHashForTwoStringsRegardlessOfOrder::hash(
                $firstString,
                $secondString
            )
        );
        Assert::assertEquals(
            SameHashForTwoStringsRegardlessOfOrder::hash(
                $firstString,
                $secondString
            ),
            SameHashForTwoStringsRegardlessOfOrder::hash(
                $secondString,
                $firstString
            )
        );
    }
}

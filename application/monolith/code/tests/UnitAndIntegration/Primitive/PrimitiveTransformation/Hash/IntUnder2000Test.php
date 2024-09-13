<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveTransformation\Hash;

use Galeas\Api\Primitive\PrimitiveTransformation\Hash\IntUnder2000;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class IntUnder2000Test extends UnitTestBase
{
    /**
     * @test
     */
    public function testUpTo2000(): void
    {
        $sha1 = sha1('0');

        $timesPerInt = [];
        for ($int = 0; $int < 2000; ++$int) {
            $timesPerInt[$int] = 0;
        }

        for ($i = 0; $i < 100000; ++$i) {
            $sha1 = sha1($sha1);
            $intHash = IntUnder2000::hash($sha1);

            if (
                $intHash >= 2000 ||
                $intHash < 0
            ) {
                Assert::fail(sprintf(
                    'String %s hashed to %s, which is above 2000',
                    $sha1,
                    strval($intHash)
                ));
            }

            $timesPerInt[$intHash] = $timesPerInt[$intHash] + 1;
        }

        $min = 1000000;
        $max = 0;
        foreach ($timesPerInt as $times) {
            if ($times < $min) {
                $min = $times;
            }
            if ($times > $max) {
                $max = $times;
            }
        }

        Assert::assertGreaterThan(10, $min, sprintf(
            'Min is %s. Should be greater than 10. Unless you are very unlucky, this means bad distribution.',
            $min
        ));

        Assert::assertLessThan(100, $max, sprintf(
            'Max is %s. Should be less than 10. Unless you are very unlucky, this means bad distribution.',
            $min
        ));
    }
}

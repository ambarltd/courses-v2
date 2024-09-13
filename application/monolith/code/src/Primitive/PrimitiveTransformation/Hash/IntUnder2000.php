<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveTransformation\Hash;

abstract class IntUnder2000
{
    /**
     * Returns an int between 0 and 1999.
     */
    public static function hash(string $string): int
    {
        $sha1 = sha1($string);
        $integer = crc32($sha1); // Can be 0 - https://stackoverflow.com/questions/25573743/can-crc32c-ever-return-to-0

        $absoluteValueInteger = abs($integer);
        $stringBetween0And9999 = str_pad(
            strval($absoluteValueInteger),
            4,
            '0',
            STR_PAD_LEFT
        );
        $integerBetween0And9999 = intval($stringBetween0And9999);
        $integerBetween0And1999 = $integerBetween0And9999 % 2000;

        // 2^32 mod 2000 = 1296
        // There will be a slight bias.
        // How big? The first 1296 will be z % more probable.
        // x = ceil(2 ^32 / 2000)
        // y = floor(2 ^32 / 2000)
        // z = ( (x/(x+y)) - (y/(x+y)) - 1 ) / 100
        // z = 0.000023283066
        // 0.000023283066 % is about 1 in 4.3 million

        return $integerBetween0And1999;
    }
}

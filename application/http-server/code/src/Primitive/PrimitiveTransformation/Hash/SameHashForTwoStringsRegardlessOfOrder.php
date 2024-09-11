<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveTransformation\Hash;

abstract class SameHashForTwoStringsRegardlessOfOrder
{
    public static function hash(
        string $firstString,
        string $secondString
    ): string {
        if ($firstString < $secondString) {
            return hash('sha512', $firstString.$secondString);
        }

        return hash('sha512', $secondString.$firstString);
    }
}

<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveCreation\Id;

abstract class IdCreator
{
    /**
     * To have a dependable length, using base64_encoding, the number of bits must be a multiple of 6.
     * The random_bytes function takes a number of bytes. So the number of bits must be a multiple of 6 and 8.
     * 42 bytes / 336 bits / approximately 10^101. Unlikely collisions, and safe from brute forcing for a while.
     *
     * There is no '=' padding because of the chosen number of bytes.
     *
     * '+' and '/' are respectively substituted by 'A' and 'B', such that the id is url safe and only uses
     * alphanumeric characters, although unfortunately reducing the effective number of bytes.
     *
     * @see https://en.wikipedia.org/wiki/Base64
     */
    public static function create(): string
    {
        $base64String = base64_encode(random_bytes(42)); // 336 / 6 = 56 characters
        $urlSafeString = str_replace('+', 'A', $base64String);
        $urlSafeString = str_replace('/', 'B', $urlSafeString);

        return $urlSafeString;
    }
    public static function createByHashingString(string $string): string
    {
        $sha512 = hash('sha512', $string);
        $truncatedHash = substr($sha512, 0, 42);

        $base64String = base64_encode($truncatedHash);
        $urlSafeString = str_replace('+', 'A', $base64String);
        $urlSafeString = str_replace('/', 'B', $urlSafeString);

        return $urlSafeString;
    }
}

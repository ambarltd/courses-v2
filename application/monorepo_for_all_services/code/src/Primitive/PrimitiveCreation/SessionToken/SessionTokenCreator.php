<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveCreation\SessionToken;

abstract class SessionTokenCreator
{
    /**
     * To have a dependable length, using base64_encoding, the number of bits must be a multiple of 6.
     * The random_bytes function takes a number of bytes. So the number of bits must be a multiple of 6 and 8.
     * 72 bytes / 576 bits / approximately 10^173. Unlikely collisions, and safe from brute forcing for a while.
     *
     * There is no '=' padding because of the chosen number of bytes.
     *
     * '+' and '/' are respectively substituted by 'A' and 'B', such that the token is url safe and only uses
     * alphanumeric characters, although unfortunately reducing the effective number of bytes.
     *
     * @see https://en.wikipedia.org/wiki/Base64
     */
    public static function create(): string
    {
        $base64String = base64_encode(random_bytes(72)); // 576 / 6 = 96 characters
        $urlSafeString = str_replace('+', 'A', $base64String);
        $urlSafeString = str_replace('/', 'B', $urlSafeString);

        return $urlSafeString;
    }
}

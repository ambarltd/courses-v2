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
     * There is no '=' padding because of the chosen number of bytes. '+' and '/' are respectively
     * substituted by '-' and '_', such that the session token is url safe.
     *
     * @see https://en.wikipedia.org/wiki/Base64
     */
    public static function create(): string
    {
        $base64String = base64_encode(random_bytes(72)); // 576 / 6 = 96 characters
        $urlSafeString = str_replace('+', '-', $base64String);
        $urlSafeString = str_replace('/', '_', $urlSafeString);

        return $urlSafeString;
    }
}

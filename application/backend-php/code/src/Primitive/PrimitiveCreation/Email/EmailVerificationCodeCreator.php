<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveCreation\Email;

use Galeas\Api\Primitive\PrimitiveCreation\NoRandomnessAvailable;
use Random\RandomException;

abstract class EmailVerificationCodeCreator
{
    /**
     * @throws NoRandomnessAvailable
     */
    public static function create(): string
    {
        try {
            return str_pad(self::cryptographicallySecureString(), 96, '0', STR_PAD_LEFT);
        } catch (RandomException $e) {
            throw new NoRandomnessAvailable();
        }
    }

    /**
     * Will return up to 96 digits.
     * 10^96 is approximately 2^318
     * Well beyond birthday problem collisions, and cryptographically safe for a while.
     *
     * @return string
     *
     * @throws RandomException
     */
    private static function cryptographicallySecureString()
    {
        // We are using 99,999,999  because it is less than (2^32)
        // In case we are in a 32 bit system
        return
            str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT). // 8 digits
            str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT). // 16 digits
            str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT). // 24 digits
            str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT). // 32 digits
            str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT). // 40 digits
            str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT). // 48 digits
            str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT). // 56 digits
            str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT). // 64 digits
            str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT). // 72 digits
            str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT). // 80 digits
            str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT). // 88 digits
            str_pad((string) random_int(0, 99_999_999), 8, '0', STR_PAD_LEFT);  // 96 digits
    }
}

<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveValidation\Security;

abstract class BCryptHashValidator
{
    public static function isValid(string $hash): bool
    {
        if (60 !== strlen($hash)) {
            return false;
        }

        // the hash contains an algorithm, a cost, a salt, and the hash itself
        $algorithm = substr($hash, 1, 2);
        $cost = substr($hash, 4, 2);
        $salt = substr($hash, 7, 22);
        $hashOnly = substr($hash, 29, 31);

        if (
            '$' !== substr($hash, 0, 1) ||
            '$' !== substr($hash, 3, 1) ||
            '$' !== substr($hash, 6, 1)
        ) {
            return false;
        }

        if ('2y' !== $algorithm) {
            return false;
        }

        if (
            false === ctype_digit($cost) ||
            intval($cost) < 10 ||
            intval($cost) > 31
        ) {
            return false;
        }

        if (1 !== preg_match('/^[\.\/0-9a-zA-Z]+$/', $salt)) {
            return false;
        }

        if (1 !== preg_match('/^[\.\/0-9a-zA-Z]+$/', $hashOnly)) {
            return false;
        }

        return true;
    }
}

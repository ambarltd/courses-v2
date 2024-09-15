<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveValidation\Id;

abstract class IdValidator
{
    public static function isValid(string $verificationCode): bool
    {
        // doing this before regex, avoids processing long strings
        if (56 !== strlen($verificationCode)) {
            return false;
        }

        if (1 === preg_match('/^[0-9a-zA-Z]+$/', $verificationCode)) {
            return true;
        }

        return false;
    }
}

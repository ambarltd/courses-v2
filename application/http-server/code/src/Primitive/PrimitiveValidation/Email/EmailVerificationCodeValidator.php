<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveValidation\Email;

abstract class EmailVerificationCodeValidator
{
    public static function isValid(string $verificationCode): bool
    {
        // doing this before regex, avoids processing long strings
        if (96 !== strlen($verificationCode)) {
            return false;
        }

        if (1 === preg_match('/^[0-9]+$/', $verificationCode)) {
            return true;
        }

        return false;
    }
}

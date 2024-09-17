<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveValidation\Email;

abstract class EmailValidator
{
    public static function isValid(string $email): bool
    {
        // doing this before filter_var, avoids processing long strings
        if (
            strlen($email) < 3 ||
            strlen($email) > 320
        ) {
            return false;
        }

        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }
}

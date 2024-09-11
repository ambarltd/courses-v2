<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveValidation\Username;

abstract class UsernameValidator
{
    public static function isValid(string $username): bool
    {
        // username minimum is 3 characters
        // username maximum is 32 characters
        // doing this before regex, avoids processing long strings
        if (
            strlen($username) > 32 ||
            strlen($username) < 3
        ) {
            return false;
        }

        if (1 === preg_match('/^[0-9a-zA-Z]+$/', $username)) {
            return true;
        }

        return false;
    }
}

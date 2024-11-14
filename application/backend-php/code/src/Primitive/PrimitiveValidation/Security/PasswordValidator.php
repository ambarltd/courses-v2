<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveValidation\Security;

abstract class PasswordValidator
{
    public static function isValid(string $password): bool
    {
        if (\strlen($password) < 6) {
            return false;
        }

        if (\strlen($password) > 64) {
            return false;
        }

        return true;
    }
}

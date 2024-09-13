<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveValidation\Security;

abstract class PasswordValidator
{
    public static function isValid(string $password): bool
    {
        if (strlen($password) < 10) {
            return false;
        }

        if (strlen($password) > 64) {
            return false;
        }

        $noLowercase = 1 !== preg_match('/[a-z]/', $password);
        $noUppercase = 1 !== preg_match('/[A-Z]/', $password);
        $noNumber = 1 !== preg_match('/[0-9]/', $password);
        $noSpecialCharacters = '' === preg_replace('/[a-zA-Z0-9]/', '', $password);

        if (
            $noLowercase ||
            $noUppercase ||
            $noNumber ||
            $noSpecialCharacters
        ) {
            return false;
        }

        return true;
    }
}

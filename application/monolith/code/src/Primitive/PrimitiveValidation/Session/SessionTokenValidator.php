<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveValidation\Session;

abstract class SessionTokenValidator
{
    public static function isValid(string $sesionToken): bool
    {
        // doing this before regex, avoids processing long strings
        if (96 !== strlen($sesionToken)) {
            return false;
        }

        if (1 === preg_match('/^[0-9a-zA-Z]+$/', $sesionToken)) {
            return true;
        }

        return false;
    }
}

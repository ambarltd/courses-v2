<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveValidation\FolderName;

abstract class FolderNameValidator
{
    public static function isValid(string $folderName): bool
    {
        // folderName minimum is 1 characters
        // folderName maximum is 256 characters
        // doing this before regex, avoids processing long strings
        if (
            strlen($folderName) > 256 ||
            strlen($folderName) < 1
        ) {
            return false;
        }

        if (1 === preg_match('/^\s+$/', $folderName)) {
            return false;
        }

        return true;
    }
}

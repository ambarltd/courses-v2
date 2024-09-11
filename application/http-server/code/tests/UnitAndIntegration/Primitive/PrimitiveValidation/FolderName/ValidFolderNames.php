<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\FolderName;

class ValidFolderNames
{
    /**
     * @return string[]
     */
    public static function listValidFolderNames(): array
    {
        return [
            'Folder Name',
            ' FolderName',
            'FolderName ',
            'Folder.Name',
            '*',
            '.',
            '/',
            '@',
            '\\',
            '123',
            'some folder',
            '1234EY',
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890'.
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890'.
            '12345678901234567890123456789012345678901234567890123456', // (max 256 characters)
            '1', // (min 1 character)
        ];
    }
}

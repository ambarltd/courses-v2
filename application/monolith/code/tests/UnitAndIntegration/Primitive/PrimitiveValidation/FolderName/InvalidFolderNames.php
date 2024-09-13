<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\FolderName;

abstract class InvalidFolderNames
{
    /**
     * @return string[]
     */
    public static function listInvalidFolderNames(): array
    {
        return [
            "\r", // only spaces forbidden
            "\n", // only spaces forbidden
            "\t", // only spaces forbidden
            "\f", // only spaces forbidden
            "\v", // only spaces forbidden
            "\r\n", // only spaces forbidden
            "\t\v", // only spaces forbidden
            "\f\v", // only spaces forbidden
            '       ', // only spaces forbidden
            '  ', // only spaces forbidden
            ' ', // only spaces forbidden
            '', // empty string forbidden
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890'.
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890'.
            '123456789012345678901234567890123456789012345678901234567', // (257 > max 256 characters)
        ];
    }
}

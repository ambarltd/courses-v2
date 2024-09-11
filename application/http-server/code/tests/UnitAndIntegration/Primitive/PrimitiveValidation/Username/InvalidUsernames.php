<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username;

abstract class InvalidUsernames
{
    /**
     * @return string[]
     */
    public static function listInvalidUsernames(): array
    {
        return [
            'Galeas Person', // space
            'D1', // too short
            '', // empty
            '@', // invalid symbol
            '@#', // invalid symbols
            'X', // too short
            'Mate ', // space
            '12', // too short (3 characters - min is 3)
            '123456789012345678901234567890123', // too long (33 characters - max is 32)
            'Mate ', // space at the end
            'Ma te ', // two spaces
            ' Mate', // beggins with space
            ' ', // one space
        ];
    }
}

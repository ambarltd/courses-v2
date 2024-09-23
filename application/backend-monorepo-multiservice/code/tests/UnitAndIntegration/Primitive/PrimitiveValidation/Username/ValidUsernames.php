<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username;

abstract class ValidUsernames
{
    /**
     * @return string[]
     */
    public static function listValidUsernames(): array
    {
        return [
            'GaleasPerson',
            'GaleasPerson2',
            'John',
            'Doe',
            'someperson',
            '1234EY',
            '12345678901234567890123456789012', // (max 32 characters)
            '123', // (min 3 characters)
        ];
    }
}

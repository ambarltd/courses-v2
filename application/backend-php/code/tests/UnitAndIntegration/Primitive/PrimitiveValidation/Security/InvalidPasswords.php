<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security;

abstract class InvalidPasswords
{
    /**
     * @return string[]
     */
    public static function listInvalidPasswords(): array
    {
        return [
            '',
            ' ',
            '12345',
            'abcdefg$I01234567890123456789012345678901234567890123456789012345',
        ];
    }
}

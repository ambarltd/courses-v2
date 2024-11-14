<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security;

abstract class ValidPasswords
{
    /**
     * @return string[]
     */
    public static function listValidPasswords(): array
    {
        return [
            'abcdef',
            'ABCDEF',
            'BlueSky12',
            'BLE BLE@',
            '   123123',
            ':@ !"~$C8n',
            'abcdefghI01234567890$',
            'abcdefghI012345678901234567890$',
            'abcdefghI0123456789012345678901234567890$',
            'abcdefghI01234567890123456789012345678901234567890$',
            'abcdefghI012345678901234567890123456789012345678901234567890$',
            'abcdefghI012345678901234567890123456789012345678901234567890123$',
        ];
    }
}

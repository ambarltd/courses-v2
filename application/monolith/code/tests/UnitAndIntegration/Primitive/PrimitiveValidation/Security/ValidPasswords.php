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
            'abcDEFg1/2',
            'DEF18AN/aS',
            'DEF 8AN/aS',
            'abcdefg%I9',
            '      /aC8',
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

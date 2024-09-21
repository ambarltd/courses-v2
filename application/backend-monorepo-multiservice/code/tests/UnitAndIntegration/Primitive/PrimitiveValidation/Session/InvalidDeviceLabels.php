<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Session;

abstract class InvalidDeviceLabels
{
    /**
     * @return string[]
     */
    public static function listInvalidDeviceLabels(): array
    {
        return [
            '', // empty string
            ' ', // one space
            'ab', // too short
            '12345678901234567890123456789012345678901234567890123456789012345', // too long
        ];
    }
}

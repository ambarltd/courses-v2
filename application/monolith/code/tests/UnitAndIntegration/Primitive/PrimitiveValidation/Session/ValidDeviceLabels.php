<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Session;

abstract class ValidDeviceLabels
{
    /**
     * @return string[]
     */
    public static function listValidDeviceLabels(): array
    {
        return [
            'abc',
            'some iphone device',
            'some android device',
            'some browser device',
            'hello world ! !"£*!&"(£*&!("£?',
            '1234567890123456789012345678901234567890123456789012345678901234',
        ];
    }
}

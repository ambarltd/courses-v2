<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveValidation\Session;

abstract class DeviceLabelValidator
{
    public static function isValid(string $deviceLabel): bool
    {
        if (strlen($deviceLabel) > 64) {
            return false;
        }
        if (strlen($deviceLabel) < 3) {
            return false;
        }

        return true;
    }
}

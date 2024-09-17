<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveValidation\Ip;

abstract class IpV4AndV6Validator
{
    public static function isValid(string $ip): bool
    {
        // doing this before filter_var, avoids processing long strings
        if (strlen($ip) > 100) {
            return false;
        }

        if (false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            return false;
        }

        return true;
    }
}

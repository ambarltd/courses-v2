<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveValidation\Ip;

abstract class PrivateAndReservedIpV4AndV6Validator
{
    public static function isValid(string $ip): bool
    {
        // doing this before filter_var, avoids processing long strings
        if (strlen($ip) > 100) {
            return false;
        }

        $isValidIp = false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
        $maybePrivateOrReserved = false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        $isValidIpAndMaybePrivateOrReserved = $isValidIp && $maybePrivateOrReserved;

        if ($isValidIpAndMaybePrivateOrReserved) {
            return true;
        }

        try {
            $ipBytes = inet_pton($ip);
            // current version of php is not doing FILTER_FLAG_NO_RES_RANGE correctly for ::/128
            if ($ipBytes === inet_pton('0000:0000:0000:0000:0000:0000:0000:0000')) {
                return true;
            }
            // current version of php is not doing FILTER_FLAG_NO_RES_RANGE correctly for ::1/128
            if ($ipBytes === inet_pton('0000:0000:0000:0000:0000:0000:0000:0001')) {
                return true;
            }
            // current version of php is not doing FILTER_FLAG_NO_RES_RANGE correctly for ::ffff:0:0/96
            if (
                inet_pton('0000:0000:0000:0000:0000:FFFF:0000:0000') <= $ipBytes &&
                $ipBytes <= inet_pton('0000:0000:0000:0000:0000:FFFF:FFFF:FFFF')
            ) {
                return true;
            }

            throw new \InvalidArgumentException();
        } catch (\Throwable $exception) { // exceptions from inet_pton
            return false;
        }
    }
}

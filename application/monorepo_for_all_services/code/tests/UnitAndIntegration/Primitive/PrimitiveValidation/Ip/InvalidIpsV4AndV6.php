<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip;

abstract class InvalidIpsV4AndV6
{
    /**
     * @return string[]
     */
    public static function listInvalidIps(): array
    {
        return [
            '1.1.1.01',
            '30.168.1.255.1',
            '127.1',
            '192.168.1.256',
            '-1.2.3.4',
            '3...3',
            '1',
            '',
            ' ',
            '1.1.1.1 ',
            '1.1.1.1:80',
            '1200::AB00:1234::2552:7777:1313',
            '1200:0000:AB00:1234:O000:2552:7777:1313',
            'http://[2001:db8:0:1]:80',
            '[2001:db8:0:1]:80',
            '2607:f380:a58:ffff:::1',
        ];
    }
}

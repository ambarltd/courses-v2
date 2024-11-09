<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Ip;

abstract class InvalidPrivateAndReservedIpsV4AndV6
{
    /**
     * @return string[]
     */
    public static function listInvalidIps(): array
    {
        // Private IPV4 ranges: 10.0.0.0/8, 172.16.0.0/12, and 192.168.0.0/16.
        // Reserved IPV4 ranges: 0.0.0.0/8, 169.254.0.0/16, 127.0.0.0/8, and 240.0.0.0/4.
        // Private IPV6 ranges: fc00::/7.
        // Reserved IPV6 ranges: ::/127, ::ffff:0:0/96, and fe80::/10.
        return [
            // outside 10.0.0.0/8
            '9.255.255.254',
            '9.255.255.255',
            '11.0.0.0',
            '11.0.0.1',
            // outside 172.16.0.0/12
            '172.15.255.254',
            '172.15.255.255',
            '172.32.0.0',
            '172.32.0.1',
            // outside 192.168.0.0/16
            '192.167.255.254',
            '192.167.255.255',
            '192.169.0.0',
            '192.169.0.1',
            // outside 0.0.0.0/8
            '1.0.0.0',
            '1.0.0.1',
            // outside 169.254.0.0/16
            '169.253.255.254',
            '169.253.255.255',
            '169.255.0.0',
            '169.255.0.1',
            // outside 127.0.0.0/8
            '126.255.255.254',
            '126.255.255.255',
            '128.0.0.0',
            // outside 240.0.0.0/4
            '239.255.255.254',
            '239.255.255.255',
            // outside fc00::/7
            'FBFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFE',
            'FBFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF',
            'FE00:0000:0000:0000:0000:0000:0000:0000',
            'FE00:0000:0000:0000:0000:0000:0000:0001',
            'Fe00::',
            'FE00::1',
            'fe00::',
            'fe00::1',
            // outside ::/127
            '0000:0000:0000:0000:0000:0000:0000:0002',
            '::2',
            // outside ::ffff:0:0/96
            '0000:0000:0000:0000:0000:FFFE:FFFF:FFFE',
            '0000:0000:0000:0000:0000:FFFE:FFFF:FFFF',
            '0000:0000:0000:0000:0001:0000:0000:0000',
            '0000:0000:0000:0000:0001:0000:0000:0001',
            '::FFFE:FFFF:FFFE',
            '::FFFE:FFFF:FFFF',
            '::fffe:ffff:fffe',
            '::fffe:ffff:ffff',
            '::1:0:0:0',
            '::1:0:0:1',
            // outside ::fe80::/10
            'FE7F:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFE',
            'FE7F:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF',
            'FEC0:0000:0000:0000:0000:0000:0000:0000',
            'FEC0:0000:0000:0000:0000:0000:0000:0001',
            'FEC0::',
            'fec0::',
            // invalid ips - bad syntax
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

<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveComparison\Email;

abstract class EquivalentEmails
{
    /**
     * @return string[][]
     */
    public static function validEmailPairsWhichAreTheSameAddress(): array
    {
        return [
            [
                'test2@galeas.com',
                'Test2@galeas.com',
            ],
            [
                'test3@galeas.com',
                'TesT3@galeas.com',
            ],
            [
                'foobar@exAmPle.com',
                'foObar@ExAmPle.com',
            ],
            [
                'fooBaR@exAmPle.com',
                'foObar@exAmPle.cOm',
            ],
            [
                'FOOBAR@EXAMPLE.COM',
                'foobar@example.com',
            ],
            [
                'FOO.BAR@SUBDOMAIN.EXAMPLE.COM',
                'foo.bar@subdomain.example.com',
            ],
        ];
    }
}

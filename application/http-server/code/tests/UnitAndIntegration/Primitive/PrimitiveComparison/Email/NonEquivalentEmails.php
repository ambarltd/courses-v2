<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveComparison\Email;

abstract class NonEquivalentEmails
{
    /**
     * @return string[][]
     */
    public static function validEmailPairsWhichAreNotTheSameAddress(): array
    {
        return [
            [
                'testt@galeas.com',
                'testu@galeas.com',
            ],
            [
                'test1@example.co.uk',
                'test1@example.co',
            ],
            [
                'foobar@example.com',
                'foobar@s.example.com',
            ],
            [
                'foobar+1@example.com',
                'foobar@example.com',
            ],
            [
                'foobar+1@example.com',
                'foobar1@example.com',
            ],
            [
                'foobar.1@example.com',
                'foobar@example.com',
            ],
            [
                'foobar.1@example.com',
                'foobar1@example.com',
            ],
        ];
    }
}

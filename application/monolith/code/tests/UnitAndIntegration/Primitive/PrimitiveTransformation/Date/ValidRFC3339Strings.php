<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveTransformation\Date;

abstract class ValidRFC3339Strings
{
    /**
     * @return string[]
     */
    public static function listValidRFC3339Strings(): array
    {
        return [
            '2005-02-15T15:52:01.01Z',
            '2005-02-15T15:52:01.01+00:00',
            '2005-02-15T15:52:01.002+00:00',
            '2005-02-15T15:52:01.002+20:00',
            '1985-04-12T23:20:50.52Z',
            '1996-12-19T16:39:57-08:00',
            '1990-12-31T23:59:60Z',
            '1990-12-31T15:59:60-08:00',
            '1937-01-01T12:00:27.87+00:20',
            '1937-01-01T12:00:27.87+00:20',
        ];
    }
}

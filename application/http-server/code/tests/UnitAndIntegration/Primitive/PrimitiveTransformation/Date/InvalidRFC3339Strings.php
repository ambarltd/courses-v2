<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveTransformation\Date;

abstract class InvalidRFC3339Strings
{
    /**
     * @return string[]
     */
    public static function listInvalidRFC3339Strings(): array
    {
        return [
            '', // empty
            ' ', // space
            '                                                        ', // spaces
            ' 1985-04-12T23:20:50.52Z', // leading space
            '1985-04-12T 23:20:50.52Z', // space in the middle
            '1985-04-12T23:20:50.52Z ', // space at the end
            '!1985-04-12T23:20:50.52Z', // leads with !
            '1985-04-12T!23:20:50.52Z', // invalid ! in the middle
            '1985-04-12T/23:20:50.52Z', // invalid / in the middle
            '12-04-1985T/23:20:50.52Z', // reverse date
            // missing parts
            '1985-04-12T23:20.52Z',
            '1985-04-12T23:20Z',
            '1985-04-12T23Z',
            '1985-04-12TZ',
            '1985-04-12T',
            '1985-04-12',
            '1985-04',
            '1985',
            '1985-04-12T23:20:50.52',
            '1985-04-12T23:20:50',
            '1985-04-12T23:20',
            '1985-04-12T23',
            '1985-04-12T',
            '1985-04-12',
            '1985-04',
            '1985',
//            overflows which should be invalid (these cases will resolve to a date at the moment)
//            '1985-04-12T23:20:50.52+24:01', // overflow timezone
//            '1985-13-12T23:20:50.52Z', // overflow month
//            '1985-05-32T23:20:50.52Z', // overflow day
//            '1985-04-31T23:20:50.52Z', // overflow day
//            '1985-04-12T24:20:50.52Z', // overflow hour
//            '1985-04-12T23:60:50.52Z', // overflow minute
//            '1985-04-12T23:20:60.52Z', // overflow second
//            '1985-April-12T23:20:60.52Z', // month as word
//            '1985-April-12T23:20:60.52Z', // overflow second
        ];
    }
}

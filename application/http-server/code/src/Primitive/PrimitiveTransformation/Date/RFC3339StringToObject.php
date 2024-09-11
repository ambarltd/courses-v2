<?php

declare(strict_types=1);

namespace Galeas\Api\Primitive\PrimitiveTransformation\Date;

abstract class RFC3339StringToObject
{
    /**
     * Note that timezones will be kept, instead of being overriden to UTC.
     * Will return null if the dateTimeString cannot be converted.
     */
    public static function transform(string $dateTimeString): ?\DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat(\DATE_RFC3339, $dateTimeString);
        if ($date instanceof \DateTimeImmutable) {
            return $date;
        }

        $date = \DateTimeImmutable::createFromFormat(\DATE_RFC3339_EXTENDED, $dateTimeString);
        if ($date instanceof \DateTimeImmutable) {
            return $date;
        }

        // RFC3339 is not working very well with milliseconds on DATE_RFC3339_EXTENDED
        // will use microseconds instead
        $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.uP', $dateTimeString);
        if ($date instanceof \DateTimeImmutable) {
            return $date;
        }

        return null;
    }
}

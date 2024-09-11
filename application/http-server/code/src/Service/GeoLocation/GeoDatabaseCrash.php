<?php

declare(strict_types=1);

namespace Galeas\Api\Service\GeoLocation;

use Galeas\Api\Common\ExceptionBase\DatabaseFailure;

class GeoDatabaseCrash extends DatabaseFailure
{
    public static function getErrorIdentifier(): string
    {
        return 'Service_GeoLocation_GeoDatabaseCrash';
    }
}

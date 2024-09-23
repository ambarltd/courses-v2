<?php

declare(strict_types=1);

namespace Galeas\Api\Service\RequestMapper\Exception;

use Galeas\Api\CommonException\InternalServerErrorException;

class InternalDateHandlingError extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Service_RequestMapper_InvalidDate';
    }
}

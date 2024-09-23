<?php

declare(strict_types=1);

namespace Galeas\Api\Service\RequestMapper\Exception;

use Galeas\Api\CommonException\BadRequestException;

class InvalidJson extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Service_RequestMapper_InvalidJson';
    }
}

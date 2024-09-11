<?php

declare(strict_types=1);

namespace Galeas\Api\JsonSchema;

use Galeas\Api\Common\ExceptionBase\InternalServerErrorException;

class ExceptionSerializerFailed extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'JsonSchema_ExceptionSerializerFailed';
    }
}

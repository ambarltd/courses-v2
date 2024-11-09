<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event\Exception;

use Galeas\Api\CommonException\InternalServerErrorException;

class EventMappingReflectionError extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Common_Event_EventMappingReflectionError';
    }
}

<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event\Exception;

use Galeas\Api\Common\ExceptionBase\BadRequestException;

class FoundBadJsonInProjectionOrReaction extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Common_Event_FoundBadJsonInProjectionOrReaction';
    }
}

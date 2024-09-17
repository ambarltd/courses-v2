<?php

declare(strict_types=1);

namespace Galeas\Api\Common\ExceptionBase;

class EventStoreCannotRead extends DatabaseFailure
{
    final public static function getErrorIdentifier(): string
    {
        return 'Common_EventStoreCannotRead';
    }
}

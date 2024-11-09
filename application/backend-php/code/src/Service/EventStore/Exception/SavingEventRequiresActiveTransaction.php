<?php

declare(strict_types=1);

namespace Galeas\Api\Service\EventStore\Exception;

use Galeas\Api\CommonException\InternalServerErrorException;

class SavingEventRequiresActiveTransaction extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Service_EventStore_SavingEventRequiresActiveTransaction';
    }
}

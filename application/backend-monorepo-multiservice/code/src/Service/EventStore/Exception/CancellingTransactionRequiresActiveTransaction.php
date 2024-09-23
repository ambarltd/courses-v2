<?php

declare(strict_types=1);

namespace Galeas\Api\Service\EventStore\Exception;

use Galeas\Api\CommonException\InternalServerErrorException;

class CancellingTransactionRequiresActiveTransaction extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Service_EventStore_CancellingTransactionRequiresActiveTransaction';
    }
}

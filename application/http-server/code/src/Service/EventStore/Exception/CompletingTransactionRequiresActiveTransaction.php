<?php

declare(strict_types=1);

namespace Galeas\Api\Service\EventStore\Exception;

use Galeas\Api\Common\ExceptionBase\InternalServerErrorException;

class CompletingTransactionRequiresActiveTransaction extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Service_EventStore_CompletingTransactionRequiresActiveTransaction';
    }
}

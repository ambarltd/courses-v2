<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact;

use Galeas\Api\Common\ExceptionBase\BadRequestException;

class CannotRequestSelf extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Messaging_Contact_RequestContact_CannotRequestSelf';
    }
}

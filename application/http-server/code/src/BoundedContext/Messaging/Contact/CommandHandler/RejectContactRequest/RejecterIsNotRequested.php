<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RejectContactRequest;

use Galeas\Api\Common\ExceptionBase\AccessDeniedException;

class RejecterIsNotRequested extends AccessDeniedException
{
    public static function getErrorIdentifier(): string
    {
        return 'Messaging_Contact_RejectContactRequest_RejecterIsNotRequested';
    }
}

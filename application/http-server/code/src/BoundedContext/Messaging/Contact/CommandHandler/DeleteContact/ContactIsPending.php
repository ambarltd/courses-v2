<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\DeleteContact;

use Galeas\Api\Common\ExceptionBase\AccessDeniedException;

class ContactIsPending extends AccessDeniedException
{
    public static function getErrorIdentifier(): string
    {
        return 'Messaging_Contact_DeleteContact_ContactIsPending';
    }
}

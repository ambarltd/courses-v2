<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\CancelContactRequest;

use Galeas\Api\Common\ExceptionBase\AccessDeniedException;

class ContactIsInactive extends AccessDeniedException
{
    public static function getErrorIdentifier(): string
    {
        return 'Messaging_Contact_CancelContactRequest_ContactIsInactive';
    }
}

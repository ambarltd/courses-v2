<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut;

use Galeas\Api\CommonException\AccessDeniedException;

class SessionUserDoesNotMatch extends AccessDeniedException
{
    public static function getErrorIdentifier(): string
    {
        return 'Security_Session_SignOut_SessionUserDoesNotMatch';
    }
}

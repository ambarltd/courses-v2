<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken;

use Galeas\Api\Common\ExceptionBase\AccessDeniedException;

class AlreadySignedOut extends AccessDeniedException
{
    public static function getErrorIdentifier(): string
    {
        return 'Security_Session_SignOut_AlreadySignedOut';
    }
}

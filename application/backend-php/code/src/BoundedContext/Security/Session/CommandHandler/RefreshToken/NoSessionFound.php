<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken;

use Galeas\Api\CommonException\AccessDeniedException;

class NoSessionFound extends AccessDeniedException
{
    public static function getErrorIdentifier(): string
    {
        return 'Security_Session_RefreshToken_NoSessionFound';
    }
}

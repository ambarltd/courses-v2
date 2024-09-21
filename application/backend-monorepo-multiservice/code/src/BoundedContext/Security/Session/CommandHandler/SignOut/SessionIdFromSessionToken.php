<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface SessionIdFromSessionToken
{
    /**
     * @throws ProjectionCannotRead
     */
    public function sessionIdFromSessionToken(string $sessionToken): ?string;
}

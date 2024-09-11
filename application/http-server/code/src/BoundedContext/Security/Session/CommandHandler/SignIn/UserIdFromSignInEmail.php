<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface UserIdFromSignInEmail
{
    /**
     * @throws ProjectionCannotRead
     */
    public function userIdFromSignInEmail(string $username): ?string;
}

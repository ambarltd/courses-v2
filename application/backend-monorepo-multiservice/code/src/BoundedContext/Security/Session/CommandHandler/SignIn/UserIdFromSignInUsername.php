<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn;

use Galeas\Api\CommonException\ProjectionCannotRead;

interface UserIdFromSignInUsername
{
    /**
     * @throws ProjectionCannotRead
     */
    public function userIdFromSignInUsername(string $username): ?string;
}

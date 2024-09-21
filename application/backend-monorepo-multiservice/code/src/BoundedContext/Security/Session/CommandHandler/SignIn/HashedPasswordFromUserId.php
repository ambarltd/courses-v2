<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface HashedPasswordFromUserId
{
    /**
     * @throws ProjectionCannotRead
     */
    public function hashedPasswordFromUserId(string $userId): ?string;
}

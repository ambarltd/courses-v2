<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface IsUsernameTaken
{
    /**
     * @throws ProjectionCannotRead
     */
    public function isUsernameTaken(string $username): bool;
}

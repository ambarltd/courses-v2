<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface IsEmailTaken
{
    /**
     * @throws ProjectionCannotRead
     */
    public function isEmailTaken(string $email): bool;
}

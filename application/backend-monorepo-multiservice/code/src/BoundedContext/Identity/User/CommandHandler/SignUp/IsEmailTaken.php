<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp;

use Galeas\Api\CommonException\ProjectionCannotRead;

interface IsEmailTaken
{
    /**
     * @throws ProjectionCannotRead
     */
    public function isEmailTaken(string $email): bool;
}

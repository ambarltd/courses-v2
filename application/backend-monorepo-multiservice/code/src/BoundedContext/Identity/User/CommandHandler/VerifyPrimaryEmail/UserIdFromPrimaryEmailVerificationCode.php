<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail;

use Galeas\Api\CommonException\ProjectionCannotRead;

interface UserIdFromPrimaryEmailVerificationCode
{
    /**
     * @throws ProjectionCannotRead
     */
    public function userIdFromPrimaryEmailVerificationCode(string $primaryEmailVerificationCode): ?string;
}

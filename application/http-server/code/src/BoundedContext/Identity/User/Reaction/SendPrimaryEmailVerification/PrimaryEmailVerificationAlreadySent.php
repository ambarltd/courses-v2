<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Reaction\SendPrimaryEmailVerification;

use Galeas\Api\Common\ExceptionBase\BadRequestException;

class PrimaryEmailVerificationAlreadySent extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Identity_User_SendPrimaryEmailVerification_PrimaryEmailVerificationAlreadySent';
    }
}

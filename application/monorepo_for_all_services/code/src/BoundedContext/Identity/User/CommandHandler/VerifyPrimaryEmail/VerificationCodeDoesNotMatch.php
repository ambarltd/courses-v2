<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail;

use Galeas\Api\Common\ExceptionBase\BadRequestException;

class VerificationCodeDoesNotMatch extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Identity_User_VerifyPrimaryEmail_VerificationCodeDoesNotMatch';
    }
}

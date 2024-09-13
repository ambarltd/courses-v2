<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail;

use Galeas\Api\Common\ExceptionBase\NotFoundException;

class NoUserFoundForCode extends NotFoundException
{
    public static function getErrorIdentifier(): string
    {
        return 'Identity_User_VerifyPrimaryEmail_NoUserFoundForCode';
    }
}

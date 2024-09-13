<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange;

use Galeas\Api\Common\ExceptionBase\BadRequestException;

class EmailIsTaken extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Identity_User_RequestPrimaryEmailChange_EmailIsTaken';
    }
}

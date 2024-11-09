<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp;

use Galeas\Api\CommonException\BadRequestException;

class InvalidUsername extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Identity_User_SignUp_InvalidUsername';
    }
}

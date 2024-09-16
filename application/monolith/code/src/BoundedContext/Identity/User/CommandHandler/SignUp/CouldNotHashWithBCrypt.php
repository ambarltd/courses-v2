<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp;

use Galeas\Api\Common\ExceptionBase\InternalServerErrorException;

class CouldNotHashWithBCrypt extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Identity_User_CouldNotHashWithBCrypt';
    }
}

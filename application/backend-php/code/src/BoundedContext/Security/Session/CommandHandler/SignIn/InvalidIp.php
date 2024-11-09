<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn;

use Galeas\Api\CommonException\InternalServerErrorException;

class InvalidIp extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Security_Session_SignIn_InvalidIp';
    }
}

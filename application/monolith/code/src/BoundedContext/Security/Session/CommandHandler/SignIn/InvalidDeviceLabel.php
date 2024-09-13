<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn;

use Galeas\Api\Common\ExceptionBase\BadRequestException;

class InvalidDeviceLabel extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Security_Session_SignIn_InvalidDeviceLabel';
    }
}

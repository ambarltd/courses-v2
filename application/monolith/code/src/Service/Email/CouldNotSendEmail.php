<?php

declare(strict_types=1);

namespace Galeas\Api\Service\Email;

use Galeas\Api\Common\ExceptionBase\InternalServerErrorException;

class CouldNotSendEmail extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Service_Email_CouldNotSendEmail';
    }
}

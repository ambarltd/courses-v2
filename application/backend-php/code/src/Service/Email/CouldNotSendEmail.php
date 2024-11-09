<?php

declare(strict_types=1);

namespace Galeas\Api\Service\Email;

use Galeas\Api\CommonException\InternalServerErrorException;

class CouldNotSendEmail extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Service_Email_CouldNotSendEmail';
    }
}

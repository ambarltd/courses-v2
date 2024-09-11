<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Id;

use Galeas\Api\Common\ExceptionBase\InternalServerErrorException;

class InvalidId extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Common_Id_InvalidId';
    }
}

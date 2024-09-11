<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Filename;

use Galeas\Api\Common\ExceptionBase\BadRequestException;

class InvalidName extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Library_Folder_CreateFolder_InvalidName';
    }
}

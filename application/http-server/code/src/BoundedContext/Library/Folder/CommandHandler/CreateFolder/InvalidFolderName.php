<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder;

use Galeas\Api\Common\ExceptionBase\BadRequestException;

class InvalidFolderName extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Library_Folder_CreateFolder_InvalidFolderName';
    }
}

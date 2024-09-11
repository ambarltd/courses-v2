<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\CommandHandler\RenameFolder;

use Galeas\Api\Common\ExceptionBase\BadRequestException;

class InvalidFolderName extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Library_Folder_RenameFolder_InvalidFolderName';
    }
}

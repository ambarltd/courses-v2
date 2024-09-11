<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder;

use Galeas\Api\Common\ExceptionBase\BadRequestException;

class FolderIsAncestorOfDestinationFolder extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Library_Folder_MoveFolder_FolderIsAncestorOfDestinationFolder';
    }
}

<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\LoggedFolderOpened\CommandHandler\LogFolderOpened;

use Galeas\Api\Common\ExceptionBase\AccessDeniedException;

class FolderNotOwned extends AccessDeniedException
{
    public static function getErrorIdentifier(): string
    {
        return 'Library_LoggedFolderOpened_LogFolderOpened_FolderNotOwned';
    }
}

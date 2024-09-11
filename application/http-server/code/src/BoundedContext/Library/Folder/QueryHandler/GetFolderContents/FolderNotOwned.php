<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetFolderContents;

use Galeas\Api\Common\ExceptionBase\AccessDeniedException;

class FolderNotOwned extends AccessDeniedException
{
    public static function getErrorIdentifier(): string
    {
        return 'Library_Folder_GetFolderContents_FolderNotOwned';
    }
}

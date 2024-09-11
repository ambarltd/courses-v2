<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetFolderContents;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface FolderContentsFromFolderId
{
    /**
     * @throws ProjectionCannotRead
     */
    public function folderContentsFromFolderId(string $folderId): array;
}

<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface IsFolderAncestorOfDestinationFolder
{
    /**
     * @throws ProjectionCannotRead
     */
    public function isFolderAncestorOfDestinationFolder(string $folderId, string $destinationFolderId): bool;
}

<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface HasFolderReachedAncestorLimit
{
    /**
     * @throws ProjectionCannotRead
     */
    public function hasFolderReachedAncestorLimit(string $folderId): bool;
}

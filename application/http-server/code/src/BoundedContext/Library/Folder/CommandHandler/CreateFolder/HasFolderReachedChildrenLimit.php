<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface HasFolderReachedChildrenLimit
{
    /**
     * @throws ProjectionCannotRead
     */
    public function hasFolderReachedChildrenLimit(string $folderId): bool;
}

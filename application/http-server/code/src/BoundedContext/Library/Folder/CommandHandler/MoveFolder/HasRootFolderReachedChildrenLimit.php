<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface HasRootFolderReachedChildrenLimit
{
    /**
     * @throws ProjectionCannotRead
     */
    public function hasRootFolderReachedChildrenLimit(string $userId): bool;
}

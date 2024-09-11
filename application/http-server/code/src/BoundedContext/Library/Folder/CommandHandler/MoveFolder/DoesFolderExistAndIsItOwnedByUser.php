<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface DoesFolderExistAndIsItOwnedByUser
{
    /**
     * @throws ProjectionCannotRead
     */
    public function doesFolderExistAndIsItOwnedByUser(string $folderId, string $userId): bool;
}

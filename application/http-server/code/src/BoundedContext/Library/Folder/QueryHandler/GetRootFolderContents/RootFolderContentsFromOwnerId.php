<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetRootFolderContents;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface RootFolderContentsFromOwnerId
{
    /**
     * @throws ProjectionCannotRead
     */
    public function rootFolderContentsFromOwnerId(string $ownerId): array;
}

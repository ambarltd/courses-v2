<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetRootFolderContents;

use Galeas\Api\BoundedContext\Library\Folder\Query\GetRootFolderContents;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class GetRootFolderContentsHandler
{
    /**
     * @var RootFolderContentsFromOwnerId
     */
    private $rootFolderContentsFromFolderId;

    public function __construct(RootFolderContentsFromOwnerId $rootFolderContentsFromFolderId)
    {
        $this->rootFolderContentsFromFolderId = $rootFolderContentsFromFolderId;
    }

    /**
     * @throws ProjectionCannotRead
     */
    public function handle(GetRootFolderContents $query): array
    {
        return $this->
            rootFolderContentsFromFolderId->
            rootFolderContentsFromOwnerId(
                $query->authorizerId
            );
    }
}

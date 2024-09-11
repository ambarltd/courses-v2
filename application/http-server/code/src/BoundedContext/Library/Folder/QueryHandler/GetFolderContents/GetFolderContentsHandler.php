<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetFolderContents;

use Galeas\Api\BoundedContext\Library\Folder\Query\GetFolderContents;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class GetFolderContentsHandler
{
    /**
     * @var DoesFolderExistAndIsItOwnedByUser
     */
    private $doesFolderExistAndIsItOwnedByUser;

    /**
     * @var FolderContentsFromFolderId
     */
    private $folderContentsFromFolderId;

    public function __construct(
        DoesFolderExistAndIsItOwnedByUser $doesFolderExistAndIsItOwnedByUser,
        FolderContentsFromFolderId $folderContentsFromFolderId
    ) {
        $this->doesFolderExistAndIsItOwnedByUser = $doesFolderExistAndIsItOwnedByUser;
        $this->folderContentsFromFolderId = $folderContentsFromFolderId;
    }

    /**
     * @throws ProjectionCannotRead|FolderNotOwned
     */
    public function handle(GetFolderContents $query): array
    {
        if (false === $this->doesFolderExistAndIsItOwnedByUser->doesFolderExistAndIsItOwnedByUser($query->folderId, $query->authorizerId)) {
            throw new FolderNotOwned();
        }

        return $this->
            folderContentsFromFolderId->
            folderContentsFromFolderId(
                $query->folderId
            );
    }
}

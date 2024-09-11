<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetRootFolderContents\RootFolderContentsFromOwnerId as GRFCRootFolderContentsFromOwnerId;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class RootFolderContentsFromOwnerId implements GRFCRootFolderContentsFromOwnerId
{
    /**
     * @var DocumentManager
     */
    private $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function rootFolderContentsFromOwnerId(string $ownerId): array
    {
        try {
            $childrenFolders = $this->projectionDocumentManager
                ->createQueryBuilder(FolderInformation::class)
                ->field('parent')->equals(null)
                ->field('ownerId')->equals($ownerId)
                ->getQuery()
                ->execute();

            if (!($childrenFolders instanceof Iterator)) {
                throw new \Exception();
            }

            $childrenFolders = $childrenFolders->toArray();

            return [
                'childrenFolders' => $this->serializeChildrenFolders($childrenFolders),
            ];
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }

    /**
     * @param FolderInformation[] $folders
     */
    private function serializeChildrenFolders(array $folders): array
    {
        $serializedFolders = [];

        foreach ($folders as $folder) {
            $serializedFolders[] = [
                'id' => $folder->folderId(),
                'name' => $folder->name(),
            ];
        }

        return $serializedFolders;
    }
}

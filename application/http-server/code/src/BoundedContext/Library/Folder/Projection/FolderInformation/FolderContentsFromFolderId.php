<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetFolderContents\FolderContentsFromFolderId as GCFolderContentsFromFolderId;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class FolderContentsFromFolderId implements GCFolderContentsFromFolderId
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
    public function folderContentsFromFolderId(string $folderId): array
    {
        try {
            $folderInformation = $this->projectionDocumentManager
                ->createQueryBuilder(FolderInformation::class)
                ->field('id')->equals($folderId)
                ->getQuery()
                ->getSingleResult();

            if (!($folderInformation instanceof FolderInformation)) {
                throw new \Exception();
            }

            $childrenFolders = $this->projectionDocumentManager
                ->createQueryBuilder(FolderInformation::class)
                ->field('parent')->equals($folderId)
                ->getQuery()
                ->execute();

            if (!($childrenFolders instanceof Iterator)) {
                throw new \Exception();
            }

            $childrenFolders = $childrenFolders->toArray();

            return [
                'folder' => [
                    'id' => $folderInformation->folderId(),
                    'name' => $folderInformation->name(),
                    'parent' => $folderInformation->parent(),
                ],
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

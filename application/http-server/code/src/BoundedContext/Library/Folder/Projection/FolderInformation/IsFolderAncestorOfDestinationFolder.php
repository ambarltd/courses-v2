<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\IsFolderAncestorOfDestinationFolder as MFIsFolderAncestorOfDestinationFolder;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class IsFolderAncestorOfDestinationFolder implements MFIsFolderAncestorOfDestinationFolder
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
     * Deleted folders are not taken into account because they (and all descendants) are deleted
     * from database records. This is handled in @see FolderInformationProcessor.
     *
     * {@inheritdoc}
     */
    public function isFolderAncestorOfDestinationFolder(string $folderId, string $destinationFolderId): bool
    {
        try {
            $ancestorIds = $this->ancestorsOfFolder($destinationFolderId, []);

            if (in_array($folderId, $ancestorIds, true)) {
                return true;
            }

            return false;
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }

    /**
     * @return string[]
     *
     * @throws \Exception
     */
    private function ancestorsOfFolder(string $folderId, array $ancestorIds): array
    {
        $folderInformation = $this->projectionDocumentManager
            ->createQueryBuilder(FolderInformation::class)
            ->field('id')->equals($folderId)
            ->getQuery()
            ->getSingleResult();

        if (
            $folderInformation instanceof FolderInformation &&
            null === $folderInformation->parent()
        ) {
            return $ancestorIds;
        }

        if (
            $folderInformation instanceof FolderInformation &&
            null !== $folderInformation->parent()
        ) {
            $ancestorIds[] = $folderInformation->parent();

            return $this->ancestorsOfFolder(
                $folderInformation->parent(),
                $ancestorIds
            );
        }

        throw new \Exception();
    }
}

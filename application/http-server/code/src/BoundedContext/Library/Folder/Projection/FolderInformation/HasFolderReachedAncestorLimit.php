<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\HasFolderReachedAncestorLimit as CFHasFolderReachedAncestorLimit;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\HasFolderReachedAncestorLimit as MFHasFolderReachedAncestorLimit;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class HasFolderReachedAncestorLimit implements CFHasFolderReachedAncestorLimit, MFHasFolderReachedAncestorLimit
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
    public function hasFolderReachedAncestorLimit(string $folderId): bool
    {
        try {
            $ancestorIds = $this->ancestorsOfFolder($folderId, []);

            // Accordingly, files will have up to 61 ancestors.
            // This prevents the file hierarchy from becoming a degenerate tree (unbalanced).
            // The number 60 was chosen to be close to a power of 2 (i.e. 64)
            if (count($ancestorIds) > 60) {
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

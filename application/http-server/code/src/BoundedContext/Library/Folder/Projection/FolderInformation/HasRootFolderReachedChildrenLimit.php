<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\HasRootFolderReachedChildrenLimit as CFHasRootFolderReachedChildrenLimit;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\HasRootFolderReachedChildrenLimit as MFHasRootFolderReachedChildrenLimit;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class HasRootFolderReachedChildrenLimit implements CFHasRootFolderReachedChildrenLimit, MFHasRootFolderReachedChildrenLimit
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
    public function hasRootFolderReachedChildrenLimit(string $userId): bool
    {
        try {
            $countChildren = $this->projectionDocumentManager
                ->createQueryBuilder(FolderInformation::class)
                ->field('parent')->equals(null)
                ->field('ownerId')->equals($userId)
                ->count()
                ->getQuery()
                ->execute();

            if (!is_int($countChildren)) {
                throw new \Exception();
            }

            // The number 65530 was chosen to be close to a power of 2 (i.e. 65536)
            // The number of files in a folder could be larger, they'd have to be paginated separately from folders,
            // and a distributed hash table would be necessary for large numbers of files.
            if ($countChildren > 65530) {
                return true;
            }

            return false;
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}

<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\DoesFolderExistAndIsItOwnedByUser as CFDoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\DeleteFolder\DoesFolderExistAndIsItOwnedByUser as DFDoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\DoesFolderExistAndIsItOwnedByUser as MFDoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\RenameFolder\DoesFolderExistAndIsItOwnedByUser as RFDoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetFolderContents\DoesFolderExistAndIsItOwnedByUser as GFCDoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\BoundedContext\Library\LoggedFolderOpened\CommandHandler\LogFolderOpened\DoesFolderExistAndIsItOwnedByUser as OFDoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class DoesFolderExistAndIsItOwnedByUser implements CFDoesFolderExistAndIsItOwnedByUser, GFCDoesFolderExistAndIsItOwnedByUser, DFDoesFolderExistAndIsItOwnedByUser, RFDoesFolderExistAndIsItOwnedByUser, MFDoesFolderExistAndIsItOwnedByUser, OFDoesFolderExistAndIsItOwnedByUser
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
     * This projection should not return a folder that has been deleted. This includes accounting for ancestors
     * being deleted. This is handled in @see FolderInformationProcessor.
     *
     * {@inheritdoc}
     */
    public function doesFolderExistAndIsItOwnedByUser(string $folderId, string $userId): bool
    {
        try {
            $folderInformation = $this->projectionDocumentManager
                ->createQueryBuilder(FolderInformation::class)
                ->field('id')->equals($folderId)
                ->getQuery()
                ->getSingleResult();

            if (
                null !== $folderInformation &&
                (!($folderInformation instanceof FolderInformation))
            ) {
                throw new \Exception();
            }

            if (
                $folderInformation instanceof FolderInformation &&
                $folderInformation->ownerId() === $userId
            ) {
                return true;
            }

            return false;
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}

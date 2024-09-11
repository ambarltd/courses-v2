<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder;

use Galeas\Api\BoundedContext\Library\Folder\Command\MoveFolder;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderMoved;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class MoveFolderHandler
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var DoesFolderExistAndIsItOwnedByUser
     */
    private $doesFolderExistAndIsItOwnedByUser;

    /**
     * @var HasRootFolderReachedChildrenLimit
     */
    private $hasRootFolderReachedChildrenLimit;

    /**
     * @var HasFolderReachedAncestorLimit
     */
    private $hasFolderReachedAncestorLimit;

    /**
     * @var HasFolderReachedChildrenLimit
     */
    private $hasFolderReachedChildrenLimit;

    /**
     * @var IsFolderAncestorOfDestinationFolder
     */
    private $isFolderAncestorOfDestinationFolder;

    public function __construct(
        EventStore $eventStore,
        Queue $queue,
        DoesFolderExistAndIsItOwnedByUser $doesFolderExistAndIsItOwnedByUser,
        HasRootFolderReachedChildrenLimit $hasRootFolderReachedChildrenLimit,
        HasFolderReachedAncestorLimit $hasFolderReachedAncestorLimit,
        HasFolderReachedChildrenLimit $hasFolderReachedChildrenLimit,
        IsFolderAncestorOfDestinationFolder $isFolderAncestorOfDestinationFolder
    ) {
        $this->eventStore = $eventStore;
        $this->queue = $queue;
        $this->doesFolderExistAndIsItOwnedByUser = $doesFolderExistAndIsItOwnedByUser;
        $this->hasRootFolderReachedChildrenLimit = $hasRootFolderReachedChildrenLimit;
        $this->hasFolderReachedAncestorLimit = $hasFolderReachedAncestorLimit;
        $this->hasFolderReachedChildrenLimit = $hasFolderReachedChildrenLimit;
        $this->isFolderAncestorOfDestinationFolder = $isFolderAncestorOfDestinationFolder;
    }

    /**
     * @throws FolderNotOwned|DestinationFolderNotOwned|DestinationFolderHasTooManyAncestors
     * @throws DestinationFolderHasTooManyChildren|FolderIsAncestorOfDestinationFolder|RootFolderHasTooManyChildren
     * @throws InvalidId|EventStoreCannotWrite|QueuingFailure|ProjectionCannotRead|QueuingFailure|EventStoreCannotRead
     */
    public function handle(MoveFolder $command): void
    {
        $this->eventStore->beginTransaction();

        $this->eventStore->find($command->folderId);

        if (false === $this->doesFolderExistAndIsItOwnedByUser->doesFolderExistAndIsItOwnedByUser($command->folderId, $command->authorizerId)) {
            throw new FolderNotOwned();
        }

        if (
            null === $command->destinationFolderId &&
            true === $this->hasRootFolderReachedChildrenLimit->hasRootFolderReachedChildrenLimit($command->authorizerId)
        ) {
            throw new RootFolderHasTooManyChildren();
        }

        if (
            null !== $command->destinationFolderId &&
            false === $this->doesFolderExistAndIsItOwnedByUser->doesFolderExistAndIsItOwnedByUser($command->destinationFolderId, $command->authorizerId)
        ) {
            throw new DestinationFolderNotOwned();
        }

        if (
            null !== $command->destinationFolderId &&
            true === $this->hasFolderReachedAncestorLimit->hasFolderReachedAncestorLimit($command->destinationFolderId)
        ) {
            throw new DestinationFolderHasTooManyAncestors();
        }

        if (
            null !== $command->destinationFolderId &&
            true === $this->hasFolderReachedChildrenLimit->hasFolderReachedChildrenLimit($command->destinationFolderId)
        ) {
            throw new DestinationFolderHasTooManyChildren();
        }

        if (
            null !== $command->destinationFolderId &&
            true === $this->isFolderAncestorOfDestinationFolder->isFolderAncestorOfDestinationFolder($command->folderId, $command->destinationFolderId)
        ) {
            throw new FolderIsAncestorOfDestinationFolder();
        }

        $folderMoved = FolderMoved::fromProperties(
            Id::fromId($command->authorizerId),
            $command->metadata,
            Id::fromId($command->folderId),
            $command->destinationFolderId ? Id::fromId($command->destinationFolderId) : null
        );

        $this->eventStore->save($folderMoved);
        $this->eventStore->completeTransaction();
        $this->queue->enqueue($folderMoved);
    }
}

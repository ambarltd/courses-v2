<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\CommandHandler\DeleteFolder;

use Galeas\Api\BoundedContext\Library\Folder\Command\DeleteFolder;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderDeleted;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class DeleteFolderHandler
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

    public function __construct(
        EventStore $eventStore,
        Queue $queue,
        DoesFolderExistAndIsItOwnedByUser $doesFolderExistAndIsItOwnedByUser
    ) {
        $this->eventStore = $eventStore;
        $this->queue = $queue;
        $this->doesFolderExistAndIsItOwnedByUser = $doesFolderExistAndIsItOwnedByUser;
    }

    /**
     * @throws FolderNotOwned
     * @throws InvalidId|EventStoreCannotWrite|QueuingFailure|ProjectionCannotRead|QueuingFailure|EventStoreCannotRead
     */
    public function handle(DeleteFolder $command): void
    {
        $this->eventStore->beginTransaction();

        $this->eventStore->find($command->folderId);

        if (false === $this->doesFolderExistAndIsItOwnedByUser->doesFolderExistAndIsItOwnedByUser($command->folderId, $command->authorizerId)) {
            throw new FolderNotOwned();
        }

        $folderDeleted = FolderDeleted::fromProperties(
            Id::fromId($command->authorizerId),
            $command->metadata,
            Id::fromId($command->folderId)
        );

        $this->eventStore->save($folderDeleted);
        $this->eventStore->completeTransaction();

        $this->queue->enqueue($folderDeleted);
    }
}

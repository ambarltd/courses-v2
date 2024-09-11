<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\CommandHandler\RenameFolder;

use Galeas\Api\BoundedContext\Library\Folder\Command\RenameFolder;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderRenamed;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Primitive\PrimitiveValidation\FolderName\FolderNameValidator;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class RenameFolderHandler
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
     * @throws InvalidFolderName|FolderNotOwned
     * @throws InvalidId|EventStoreCannotWrite|QueuingFailure|ProjectionCannotRead|QueuingFailure|EventStoreCannotRead
     */
    public function handle(RenameFolder $command): void
    {
        $this->eventStore->beginTransaction();

        $this->eventStore->find($command->folderId);

        if (false === FolderNameValidator::isValid($command->name)) {
            throw new InvalidFolderName();
        }

        if (false === $this->doesFolderExistAndIsItOwnedByUser->doesFolderExistAndIsItOwnedByUser($command->folderId, $command->authorizerId)) {
            throw new FolderNotOwned();
        }

        $folderRenamed = FolderRenamed::fromProperties(
            Id::fromId($command->authorizerId),
            $command->metadata,
            Id::fromId($command->folderId),
            $command->name
        );

        $this->eventStore->save($folderRenamed);
        $this->eventStore->completeTransaction();

        $this->queue->enqueue($folderRenamed);
    }
}

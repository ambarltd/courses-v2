<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\LoggedFolderOpened\CommandHandler\LogFolderOpened;

use Galeas\Api\BoundedContext\Library\LoggedFolderOpened\Command\LogFolderOpened;
use Galeas\Api\BoundedContext\Library\LoggedFolderOpened\Event\LoggedFolderOpened;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class LogFolderOpenedHandler
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
     * @throws InvalidId|EventStoreCannotWrite|QueuingFailure|ProjectionCannotRead|QueuingFailure
     */
    public function handle(LogFolderOpened $command): array
    {
        $this->eventStore->beginTransaction();

        if (false === $this->doesFolderExistAndIsItOwnedByUser->doesFolderExistAndIsItOwnedByUser($command->folderId, $command->authorizerId)) {
            throw new FolderNotOwned();
        }

        $folderOpened = LoggedFolderOpened::fromProperties(
            Id::fromId($command->authorizerId),
            $command->metadata,
            Id::fromId($command->folderId)
        );

        $this->eventStore->save($folderOpened);
        $this->eventStore->completeTransaction();
        $this->queue->enqueue($folderOpened);

        return [
            'folderOpenedId' => $folderOpened->aggregateId()->id(),
        ];
    }
}

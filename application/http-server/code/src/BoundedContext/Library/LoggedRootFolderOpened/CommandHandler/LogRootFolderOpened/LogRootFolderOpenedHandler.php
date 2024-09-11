<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\LoggedRootFolderOpened\CommandHandler\LogRootFolderOpened;

use Galeas\Api\BoundedContext\Library\LoggedRootFolderOpened\Command\LogRootFolderOpened;
use Galeas\Api\BoundedContext\Library\LoggedRootFolderOpened\Event\LoggedRootFolderOpened;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class LogRootFolderOpenedHandler
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var Queue
     */
    private $queue;

    public function __construct(
        EventStore $eventStore,
        Queue $queue
    ) {
        $this->eventStore = $eventStore;
        $this->queue = $queue;
    }

    /**
     * @throws InvalidId|EventStoreCannotWrite|QueuingFailure|QueuingFailure
     */
    public function handle(LogRootFolderOpened $command): void
    {
        $this->eventStore->beginTransaction();

        $rootFolder = LoggedRootFolderOpened::fromProperties(
            Id::fromId($command->authorizerId),
            $command->metadata
        );

        $this->eventStore->save($rootFolder);
        $this->eventStore->completeTransaction();
        $this->queue->enqueue($rootFolder);
    }
}

<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\DeleteOneToOneConversation;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Aggregate\OneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command\DeleteOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationDeletedBySender;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class DeleteOneToOneConversationHandler
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
     * @throws ConversationDoesNotExist|OnlySenderCanDelete|CannotDeleteAnymore|InvalidId
     * @throws EventStoreCannotWrite|EventStoreCannotRead|QueuingFailure
     */
    public function handle(DeleteOneToOneConversation $command): void
    {
        $conversationId = $command->conversationId;

        $this->eventStore->beginTransaction();
        $conversation = $this->eventStore->find($conversationId);

        if (!($conversation instanceof OneToOneConversation)) {
            throw new ConversationDoesNotExist();
        }

        if ($conversation->sender()->id() !== $command->authorizerId) {
            throw new OnlySenderCanDelete();
        }

        if (
            (!$conversation->pushStatus()->isPushed()) &&
            (!$conversation->pushStatus()->isPulledBySender())
        ) {
            throw new CannotDeleteAnymore();
        }

        $event = OneToOneConversationDeletedBySender::fromProperties(
            Id::fromId($command->conversationId),
            Id::fromId($command->authorizerId),
            $command->metadata
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();

        $this->queue->enqueue($event);
    }
}

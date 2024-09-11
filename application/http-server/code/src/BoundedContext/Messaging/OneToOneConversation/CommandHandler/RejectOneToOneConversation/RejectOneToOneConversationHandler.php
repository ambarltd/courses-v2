<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\RejectOneToOneConversation;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Aggregate\OneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command\RejectOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationRejectedByRecipient;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class RejectOneToOneConversationHandler
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
     * @throws ConversationDoesNotExist|OnlyRecipientCanReject|ConversationIsExpired|CannotRejectAnymore
     * @throws EventStoreCannotRead|EventStoreCannotWrite|QueuingFailure
     */
    public function handle(RejectOneToOneConversation $command): void
    {
        $conversationId = $command->conversationId;

        $this->eventStore->beginTransaction();
        $conversation = $this->eventStore->find($conversationId);

        if (!($conversation instanceof OneToOneConversation)) {
            throw new ConversationDoesNotExist();
        }

        if ($conversation->recipient()->id() !== $command->authorizerId) {
            throw new OnlyRecipientCanReject();
        }

        if (
            null !== $conversation->expirationDate() &&
            $conversation->expirationDate() < new \DateTimeImmutable()
        ) {
            throw new ConversationIsExpired();
        }

        if (!$conversation->pushStatus()->isPushed()) {
            throw new CannotRejectAnymore();
        }

        $event = OneToOneConversationRejectedByRecipient::fromProperties(
            $conversation->id(),
            $conversation->recipient(),
            $command->metadata
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();

        $this->queue->enqueue($event);
    }
}

<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command\StartOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationStarted;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Primitive\PrimitiveTransformation\Date\RFC3339StringToObject;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class StartOneToOneConversationHandler
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
     * @var AreParticipantsContacts
     */
    private $areParticipantsContacts;

    public function __construct(
        EventStore $eventStore,
        Queue $queue,
        AreParticipantsContacts $doesContactExist
    ) {
        $this->eventStore = $eventStore;
        $this->queue = $queue;
        $this->areParticipantsContacts = $doesContactExist;
    }

    /**
     * @throws CannotHaveConversationWithSelf|ParticipantsAreNotContacts|ExpirationDateIsInThePast
     * @throws MaxNumberOfViewsIsTooLarge|MaxNumberOfViewsIsTooSmall|InvalidRFC3339String
     * @throws ProjectionCannotRead|InvalidId|EventStoreCannotWrite|QueuingFailure
     */
    public function handle(StartOneToOneConversation $command): void
    {
        $sender = $command->authorizerId;
        $recipient = $command->recipient;
        if (is_string($command->expirationDate)) {
            $expirationDate = RFC3339StringToObject::transform($command->expirationDate);
            if (null === $expirationDate) {
                throw new InvalidRFC3339String();
            }
        } else {
            $expirationDate = null;
        }
        $maxNumberOfViews = $command->maxNumberOfViews;

        if ($sender === $recipient) {
            throw new CannotHaveConversationWithSelf();
        }

        if (false === $this->areParticipantsContacts->areParticipantsContacts($sender, $recipient)) {
            throw new ParticipantsAreNotContacts();
        }

        if (
            null !== $expirationDate &&
            $expirationDate < new \DateTimeImmutable()
        ) {
            throw new ExpirationDateIsInThePast();
        }

        if (
            null !== $maxNumberOfViews &&
            $maxNumberOfViews > 1000000
        ) {
            throw new MaxNumberOfViewsIsTooLarge();
        }

        if (
            null !== $maxNumberOfViews &&
            $maxNumberOfViews < 1
        ) {
            throw new MaxNumberOfViewsIsTooSmall();
        }

        $event = OneToOneConversationStarted::fromProperties(
            Id::fromId($command->authorizerId),
            $command->metadata,
            Id::fromId($sender),
            Id::fromId($recipient),
            $expirationDate,
            $maxNumberOfViews
        );

        $this->eventStore->beginTransaction();
        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();

        $this->queue->enqueue($event);
    }
}

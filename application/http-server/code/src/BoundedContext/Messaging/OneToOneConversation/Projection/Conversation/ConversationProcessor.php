<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Projection\Conversation;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationDeletedBySender;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationPulledBySender;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationRejectedByRecipient;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationStarted;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\ProjectionEventProcessor;

class ConversationProcessor implements ProjectionEventProcessor
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
     * {@inheritdoc}
     */
    public function process(Event $event): void
    {
        try {
            $conversationId = $event->aggregateId()->id();

            if ($event instanceof OneToOneConversationStarted) {
                $conversationId = $event->aggregateId()->id();
                $sender = $event->sender()->id();
                $recipient = $event->recipient()->id();
                $expirationDate = $event->expirationDate();
                $maxNumberOfViews = $event->maxNumberOfViews();

                $this->projectionDocumentManager->persist(
                    Conversation::fromProperties(
                        $conversationId,
                        $sender,
                        $recipient,
                        $expirationDate,
                        $maxNumberOfViews,
                        $event->eventOccurredOn()
                    )
                );

                $this->projectionDocumentManager->flush();
            }

            if (
                $event instanceof OneToOneConversationPulledBySender &&
                ($conversation = $this->getConversationFromId($conversationId))
            ) {
                $conversation->pulledBySender();
                $this->projectionDocumentManager->flush();
            }

            if (
                $event instanceof OneToOneConversationRejectedByRecipient &&
                ($conversation = $this->getConversationFromId($conversationId))
            ) {
                $conversation->rejectedByRecipient();
                $this->projectionDocumentManager->flush();
            }

            if (
                $event instanceof OneToOneConversationDeletedBySender &&
                ($conversation = $this->getConversationFromId($conversationId))
            ) {
                $conversation->deletedBySender();
                $this->projectionDocumentManager->flush();
            }
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }

    /**
     * @throws \Exception
     */
    private function getConversationFromId(string $conversationId): ?Conversation
    {
        $queryBuilder = $this->projectionDocumentManager
            ->createQueryBuilder(Conversation::class)
            ->field('id')->equals($conversationId);

        $conversation = $queryBuilder->getQuery()->getSingleResult();

        if (
            null !== $conversation &&
            !($conversation instanceof Conversation)
        ) {
            throw new \Exception();
        }

        return $conversation;
    }
}

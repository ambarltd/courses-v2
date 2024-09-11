<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Projection\Conversation;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\QueryHandler\ListOneToOneConversation\ConversationListFromUserId as QueryHandlerConversationListFromUserId;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class ConversationListFromUserId implements QueryHandlerConversationListFromUserId
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
    public function conversationListFromUserId(string $userId): array
    {
        try {
            $queryBuilder = $this->projectionDocumentManager
                ->createQueryBuilder(Conversation::class);

            $query = $queryBuilder
                ->addOr(
                    $queryBuilder->expr()
                        ->addAnd(
                            $queryBuilder->expr()
                                ->field('senderId')->equals($userId)
                        )
                        ->addAnd(
                            $queryBuilder->expr()
                                ->addOr(
                                    $queryBuilder->expr()
                                        ->field('pushStatus')->equals('pushed')
                                )
                                ->addOr(
                                    $queryBuilder->expr()
                                        ->field('pushStatus')->equals('pulled_by_sender')
                                )
                                ->addOr(
                                    $queryBuilder->expr()
                                        ->field('pushStatus')->equals('rejected_by_recipient')
                                )
                        )
                )
                ->addOr(
                    $queryBuilder->expr()
                        ->addAnd(
                            $queryBuilder->expr()
                                ->field('recipientId')->equals($userId)
                        )
                        ->addAnd(
                            $queryBuilder->expr()
                                ->addOr(
                                    $queryBuilder->expr()
                                        ->field('pushStatus')->equals('pushed')
                                )
                        )
                        ->addAnd(
                            $queryBuilder->expr()
                                ->addOr(
                                    $queryBuilder->expr()
                                        ->field('expirationDate')->equals(null)
                                )
                                ->addOr(
                                    $queryBuilder->expr()
                                        ->field('expirationDate')->gt(new \DateTimeImmutable())
                                )
                        )
                )
                ->sort('latestActivity', 'desc')
                ->getQuery();

            $conversations = $query->execute();

            if ($conversations instanceof Iterator) {
                $conversations = $conversations->toArray();
            } else {
                throw new \Exception();
            }

            $serializedConversations = [];

            foreach ($conversations as $conversation) {
                if (!$conversation instanceof Conversation) {
                    throw new \Exception();
                }
                $serializedConversations[] = $conversation->serialize();
            }

            return $serializedConversations;
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}

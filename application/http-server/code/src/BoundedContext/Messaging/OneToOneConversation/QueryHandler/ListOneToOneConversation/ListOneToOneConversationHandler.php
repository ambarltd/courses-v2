<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\QueryHandler\ListOneToOneConversation;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Query\ListOneToOneConversation;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class ListOneToOneConversationHandler
{
    /**
     * @var ConversationListFromUserId
     */
    private $conversationListFromUserId;

    public function __construct(ConversationListFromUserId $conversationListFromUserId)
    {
        $this->conversationListFromUserId = $conversationListFromUserId;
    }

    /**
     * @throws ProjectionCannotRead
     */
    public function handle(ListOneToOneConversation $listOneToOneConversation): array
    {
        return $this->conversationListFromUserId
            ->conversationListFromUserId(
                $listOneToOneConversation->authorizerId
            );
    }
}

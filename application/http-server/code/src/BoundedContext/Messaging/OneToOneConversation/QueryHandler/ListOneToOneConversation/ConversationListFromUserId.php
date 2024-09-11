<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\QueryHandler\ListOneToOneConversation;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface ConversationListFromUserId
{
    /**
     * @throws ProjectionCannotRead
     */
    public function ConversationListFromUserId(string $userId): array;
}

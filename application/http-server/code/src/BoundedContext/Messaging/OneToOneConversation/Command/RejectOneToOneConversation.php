<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command;

class RejectOneToOneConversation
{
    /**
     * @var string
     */
    public $authorizerId;

    /**
     * @var string
     */
    public $conversationId;

    /**
     * @var array
     */
    public $metadata;
}

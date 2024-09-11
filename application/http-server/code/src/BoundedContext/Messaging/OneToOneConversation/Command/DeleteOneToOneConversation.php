<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command;

class DeleteOneToOneConversation
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

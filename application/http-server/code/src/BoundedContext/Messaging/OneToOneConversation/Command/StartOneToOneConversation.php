<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command;

class StartOneToOneConversation
{
    /**
     * @var string
     */
    public $authorizerId;

    /**
     * @var string
     */
    public $recipient;

    /**
     * @var string|null
     */
    public $expirationDate;

    /**
     * @var int|null
     */
    public $maxNumberOfViews;

    /**
     * @var array
     */
    public $metadata;
}

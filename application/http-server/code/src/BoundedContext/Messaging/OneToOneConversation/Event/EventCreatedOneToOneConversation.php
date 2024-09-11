<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Aggregate\OneToOneConversation;
use Galeas\Api\Common\Event\Event;

interface EventCreatedOneToOneConversation extends Event
{
    public function createOneToOneConversation(): OneToOneConversation;
}

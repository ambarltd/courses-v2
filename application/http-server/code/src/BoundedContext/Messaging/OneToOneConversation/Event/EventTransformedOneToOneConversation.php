<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Aggregate\OneToOneConversation;
use Galeas\Api\Common\Event\Event;

interface EventTransformedOneToOneConversation extends Event
{
    public function transformOneToOneConversation(OneToOneConversation $oneToOneConversation): OneToOneConversation;
}

<?php

declare(strict_types=1);

namespace Galeas\Api\Service\QueueProcessor;

use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ReactionCannotProcess;

interface EventReactor
{
    /**
     * @throws ReactionCannotProcess
     */
    public function react(Event $event): void;
}

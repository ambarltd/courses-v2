<?php

declare(strict_types=1);

namespace Galeas\Api\Service\QueueProcessor;

use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ReactionCannotProcess;

interface ReactionEventProcessor
{
    /**
     * @throws ReactionCannotProcess
     */
    public function process(Event $event): void;
}

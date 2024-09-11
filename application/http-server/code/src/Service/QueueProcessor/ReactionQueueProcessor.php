<?php

declare(strict_types=1);

namespace Galeas\Api\Service\QueueProcessor;

use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ReactionCannotProcess;

class ReactionQueueProcessor
{
    /**
     * @var ReactionEventProcessor[]
     */
    private $eventProcessors;

    /**
     * @param iterable<ReactionEventProcessor> $eventProcessors
     */
    public function __construct(iterable $eventProcessors)
    {
        $this->eventProcessors = [];
        foreach ($eventProcessors as $eventProcessor) {
            $this->eventProcessors[] = $eventProcessor;
        }
    }

    /**
     * @throws ReactionCannotProcess
     */
    public function process(Event $event): void
    {
        foreach ($this->eventProcessors as $eventProcessor) {
            $eventProcessor->process($event);
        }
    }
}

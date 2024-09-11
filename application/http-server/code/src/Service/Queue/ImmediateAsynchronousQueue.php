<?php

declare(strict_types=1);

namespace Galeas\Api\Service\Queue;

use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Service\QueueProcessor\ProjectionQueueProcessor;
use Galeas\Api\Service\QueueProcessor\ReactionQueueProcessor;

/**
 * Excuse the oxymoron, this queue is immediate because it doesn't go to an external queue (i.e. Kafka).
 * Yet it's asynchronous because its operations are not wrapped in the same transactions as those of the event store.
 */
class ImmediateAsynchronousQueue implements Queue
{
    /**
     * @var ProjectionQueueProcessor
     */
    private $projectionQueueProcessor;

    /**
     * @var ReactionQueueProcessor
     */
    private $reactionQueueProcessor;

    public function __construct(
        ProjectionQueueProcessor $projectionQueueProcessor,
        ReactionQueueProcessor $reactionQueueProcessor
    ) {
        $this->projectionQueueProcessor = $projectionQueueProcessor;
        $this->reactionQueueProcessor = $reactionQueueProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(Event $event): void
    {
        try {
            $this->projectionQueueProcessor->process($event);
            $this->reactionQueueProcessor->process($event);
        } catch (\Throwable $exception) {
            throw new QueuingFailure($exception);
        }
    }
}

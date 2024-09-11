<?php

declare(strict_types=1);

namespace Galeas\Api\Service\Queue;

use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;

/**
 * Used for command handler testing. Emulates the behavior of @see KafkaQueue
 * If the behavior of this queue is similar to the kafka version,
 * then this one can be used as a substitute for testing purposes. Not only does
 * this reduce infrastructure concerns while testing, but it also reduces the testing time.
 * For integration or end to end tests, the api endpoints themselves would be tested.
 */
class InMemoryQueue implements Queue
{
    /**
     * @var Event[]
     */
    private $queuedEvents = [];

    /**
     * {@inheritdoc}
     */
    public function enqueue(Event $event): void
    {
        if (count($this->queuedEvents) > 10000000) {
            throw new QueuingFailure(new \RuntimeException('Too many events in memory'));
        }
        $this->queuedEvents[] = $event;
    }

    /**
     * @return Event[]
     */
    public function queuedEvents(): array
    {
        return $this->queuedEvents;
    }
}

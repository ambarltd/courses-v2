<?php

declare(strict_types=1);

namespace Galeas\Api\Service\EventStore;

use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Id\Id;

class AggregateAndEventIdsInLastEvent
{
    private Aggregate $aggregate;

    private Id $correlationIdInLastEvent;

    private Id $eventIdInLastEvent;

    private function __construct() {}

    // needs amending to give the last event's id and correlation id
    public static function fromProperties(Aggregate $aggregate, Id $correlationIdInLastEvent, Id $eventIdInLastEvent): self
    {
        $aggregateAndEventIdsInLastEvent = new self();
        $aggregateAndEventIdsInLastEvent->aggregate = $aggregate;
        $aggregateAndEventIdsInLastEvent->correlationIdInLastEvent = $correlationIdInLastEvent;
        $aggregateAndEventIdsInLastEvent->eventIdInLastEvent = $eventIdInLastEvent;

        return $aggregateAndEventIdsInLastEvent;
    }

    public function aggregate(): Aggregate
    {
        return $this->aggregate;
    }

    public function correlationIdInLastEvent(): Id
    {
        return $this->correlationIdInLastEvent;
    }

    public function eventIdInLastEvent(): Id
    {
        return $this->eventIdInLastEvent;
    }
}

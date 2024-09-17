<?php

declare(strict_types=1);

namespace Galeas\Api\Service\EventStore;

use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Id\Id;

class AggregateAndEventIds
{
    private Aggregate $aggregate;

    private Id $firstEventId;

    private Id $lastEventId;

    private function __construct()
    {}

    public static function fromProperties(Aggregate $aggregate, Id $firstEventId, Id $lastEventId): self
    {
        $aggregateAndEventIds = new self();
        $aggregateAndEventIds->aggregate = $aggregate;
        $aggregateAndEventIds->firstEventId = $firstEventId;
        $aggregateAndEventIds->lastEventId = $lastEventId;

        return $aggregateAndEventIds;
    }

    public function aggregate(): Aggregate
    {
        return $this->aggregate;
    }

    public function firstEventId(): Id
    {
        return $this->firstEventId;
    }

    public function lastEventId(): Id
    {
        return $this->lastEventId;
    }

}
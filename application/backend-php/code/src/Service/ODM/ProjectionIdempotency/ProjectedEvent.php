<?php

declare(strict_types=1);

namespace Galeas\Api\Service\ODM\ProjectionIdempotency;

use Galeas\Api\Common\Event\Event;

class ProjectedEvent
{
    /** @phpstan-ignore-next-line */
    private string $id;
    private string $eventId;
    private string $projectionName;

    private function __construct() {}

    public static function new(Event $event, string $projectionName): self
    {
        $projectedEvent = new self();
        $projectedEvent->eventId = $event->eventId()->id();
        $projectedEvent->projectionName = $projectionName;

        return $projectedEvent;
    }
}

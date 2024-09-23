<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Event\Exception as EventException;

abstract class AggregateFromEvents extends EventReflectionBaseClass
{
    /**
     * @param Event[] $transformationEvents
     *
     * @throws EventException\NoCreationMethodFound
     * @throws EventException\NoTransformationMethodFound
     * @throws EventException\EventMappingReflectionError
     */
    public static function aggregateFromEvents(
        Event $creationEvent,
        array $transformationEvents
    ): Aggregate {
        $creationMethod = self::eventClassToCreationMethodName($creationEvent::class);
        $aggregate = $creationEvent->{$creationMethod}();

        foreach ($transformationEvents as $transformationEvent) {
            $transformationMethod = self::eventClassToTransformationMethodName($transformationEvent::class);
            $aggregate = $transformationEvent->{$transformationMethod}($aggregate);
        }

        return $aggregate;
    }
}

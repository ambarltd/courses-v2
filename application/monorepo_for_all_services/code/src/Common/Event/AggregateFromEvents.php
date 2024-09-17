<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

use Galeas\Api\BoundedContext\Identity\User;
use Galeas\Api\BoundedContext\Security\Session;
use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Event\Exception as EventException;

abstract class AggregateFromEvents extends EventReflectionBaseClass
{
    /**
     * @param Event[] $transformationEvents
     *
     * @throws EventException\NoCreationMethodFound
     * @throws EventException\NoTransformationMethodFound
     */
    public static function aggregateFromEvents(
        Event $creationEvent,
        array $transformationEvents
    ): Aggregate {
        $creationMethod = self::eventClassToCreationMethodName(get_class($creationEvent));
        $aggregate = $creationEvent->$creationMethod();

        foreach ($transformationEvents as $transformationEvent) {
            $transformationMethod = self::eventClassToTransformationMethodName(get_class($transformationEvent));
            $aggregate = $transformationEvent->$transformationMethod($aggregate);
        }

        return $aggregate;
    }
}

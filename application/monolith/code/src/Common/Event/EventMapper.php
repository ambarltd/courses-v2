<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

use Galeas\Api\BoundedContext\Identity\User;
use Galeas\Api\BoundedContext\Security\Session;
use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Event\Exception as EventException;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;

/**
 * When creating EventCreatedAggregate and EventTransformedAggregate interfaces,
 * edit @see EventMapper::aggregateFromEvents().
 *
 * When creating events for existing interfaces,
 * edit @see EventMapper::$eventNamesToEventClasses
 *
 * Note that \DateTimeImmutable objects in occurredOn are saved without timezone and forced as UTC.
 */
abstract class EventMapper
{
    /**
     * @var string[]
     */
    private static $eventNamesToEventClasses = [
        'Identity_User_SignedUp' => User\Event\SignedUp::class,
        'Identity_User_PrimaryEmailVerified' => User\Event\PrimaryEmailVerified::class,
        'Identity_User_PrimaryEmailChangeRequested' => User\Event\PrimaryEmailChangeRequested::class,
        'Security_Session_SignedIn' => Session\Event\SignedIn::class,
        'Security_Session_TokenRefreshed' => Session\Event\TokenRefreshed::class,
        'Security_Session_SignedOut' => Session\Event\SignedOut::class,
    ];

    /**
     * @var string[]
     */
    private static $eventClassesToEventNames = null;

    /**
     * @var \ReflectionMethod[]
     */
    private static $eventNamesToReflectionConstructorMethodsCache = [];

    /**
     * @throws EventException\EventMappingReflectionError
     */
    private static function setup(): void
    {
        try {
            if (null === self::$eventClassesToEventNames) {
                self::$eventClassesToEventNames = array_flip(self::$eventNamesToEventClasses);

                foreach (self::$eventNamesToEventClasses as $eventName => $eventClass) {
                    $reflectionClass = new \ReflectionClass($eventClass);
                    $reflectionConstructorMethod = $reflectionClass->getMethod('reflectionConstructor');
                    $reflectionConstructorMethod->setAccessible(true);
                    self::$eventNamesToReflectionConstructorMethodsCache[$eventName] = $reflectionConstructorMethod;
                }
            }
        } catch (\ReflectionException $exception) {
            throw new EventException\EventMappingReflectionError('Reflection method failure');
        }
    }

    /**
     * @throws EventException\NoEventClassMappingFound
     * @throws EventException\EventMappingReflectionError
     */
    private static function eventClassToEventName(string $eventClass): string
    {
        self::setup();

        if (array_key_exists($eventClass, self::$eventClassesToEventNames)) {
            return self::$eventClassesToEventNames[$eventClass];
        }

        throw new EventException\NoEventClassMappingFound('No mapping found for class: '.$eventClass);
    }

    /**
     * @throws EventException\NoEventReflectionClassMappingMethodFound
     * @throws EventException\EventMappingReflectionError
     */
    private static function eventNameToReflectionClassMethod(string $eventName): \ReflectionMethod
    {
        self::setup();

        if (array_key_exists($eventName, self::$eventNamesToReflectionConstructorMethodsCache)) {
            return self::$eventNamesToReflectionConstructorMethodsCache[$eventName];
        }

        throw new EventException\NoEventReflectionClassMappingMethodFound('No mapping found for event name '.$eventName);
    }

    // SECTION - SERIALIZED EVENT TO EVENT

    /**
     * @throws InvalidId
     * @throws EventException\EventMappingReflectionError
     * @throws EventException\NoEventReflectionClassMappingMethodFound
     */
    private static function serializedEventToEvent(SerializedEvent $serializedEvent): Event
    {
        $reflectionClassMethod = self::eventNameToReflectionClassMethod($serializedEvent->eventName());

        $event = $reflectionClassMethod->invoke(
            null,
            Id::fromId($serializedEvent->eventId()),
            \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s.u',
                $serializedEvent->eventOccurredOn()
            ),
            Id::fromId($serializedEvent->aggregateId()),
            is_null($serializedEvent->authorizerId()) ? null : Id::fromId($serializedEvent->authorizerId()),
            is_null($serializedEvent->sourceEventId()) ? null : Id::fromId($serializedEvent->sourceEventId()),
            PayloadMapper::jsonPayloadToArrayPayload($serializedEvent->jsonMetadata()),
            PayloadMapper::jsonPayloadToArrayPayload($serializedEvent->jsonPayload())
        );

        if (!($event instanceof Event)) {
            throw new EventException\EventMappingReflectionError('Reflection method failure');
        }

        return $event;
    }

    /**
     * @param SerializedEvent[] $serializedEvents
     *
     * @return Event[]
     *
     * @throws EventException\EventMappingReflectionError
     * @throws EventException\NoEventReflectionClassMappingMethodFound
     * @throws InvalidId
     */
    public static function serializedEventsToEvents(array $serializedEvents): array
    {
        return array_map(
            function (SerializedEvent $serializedEvent): Event {
                return self::serializedEventToEvent($serializedEvent);
            },
            $serializedEvents
        );
    }

    // SECTION - JSON EVENTS TO EVENT

    /**
     * @throws InvalidId
     * @throws EventException\EventMappingReflectionError
     * @throws EventException\JsonEventEncodingError
     * @throws EventException\NoEventReflectionClassMappingMethodFound
     */
    private static function jsonEventToEvent(string $jsonEvent): Event
    {
        $jsonEventArray = json_decode($jsonEvent, true);

        $reflectionClassMethod = self::eventNameToReflectionClassMethod($jsonEventArray['eventName']);

        $stringMetadata = json_encode($jsonEventArray['metadata']);
        $payloadMetadata = json_encode($jsonEventArray['payload']);

        if (
            is_bool($stringMetadata) ||
            is_bool($payloadMetadata)
        ) {
            throw new EventException\JsonEventEncodingError('JSON encoding error');
        }

        $event = $reflectionClassMethod->invoke(
            null,
            Id::fromId($jsonEventArray['eventId']),
            \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s.u',
                $jsonEventArray['eventOccurredOn']
            ),
            Id::fromId($jsonEventArray['aggregateId']),
            is_null($jsonEventArray['authorizerId']) ? null : Id::fromId($jsonEventArray['authorizerId']),
            is_null($jsonEventArray['sourceEventId']) ? null : Id::fromId($jsonEventArray['sourceEventId']),
            PayloadMapper::jsonPayloadToArrayPayload($stringMetadata),
            PayloadMapper::jsonPayloadToArrayPayload($payloadMetadata)
        );

        if (!($event instanceof Event)) {
            throw new EventException\EventMappingReflectionError('Reflection method failure');
        }

        return $event;
    }

    /**
     * @param string[] $jsonEvents
     *
     * @return Event[]
     *
     * @throws EventException\EventMappingReflectionError
     * @throws EventException\JsonEventEncodingError
     * @throws EventException\NoEventReflectionClassMappingMethodFound
     * @throws InvalidId
     */
    public static function jsonEventsToEvents(array $jsonEvents): array
    {
        return array_map(
            function (string $jsonEvent): Event {
                return self::jsonEventToEvent($jsonEvent);
            },
            $jsonEvents
        );
    }

    // SECTION - EVENT TO SERIALIZED EVENT

    /**
     * @throws EventException\PropertyIsOfInvalidType
     */
    private static function arrayPayloadFromEvent(Event $event): array
    {
        $properties = (new \ReflectionObject($event))
            ->getProperties(
                \ReflectionProperty::IS_PUBLIC + \ReflectionProperty::IS_PROTECTED + \ReflectionProperty::IS_PRIVATE
            );

        $propertyNamesAndValues = [];
        foreach ($properties as $property) {
            $propertyIsNotAccessible = $property->isPrivate() || $property->isProtected();
            if ($propertyIsNotAccessible) {
                $property->setAccessible(true);
            }

            $propertyNamesAndValues[$property->getName()] = $property->getValue($event);

            if ($propertyIsNotAccessible) {
                $property->setAccessible(false);
            }
        }

        $payload = [];
        $skipProperties = [
            'eventId',
            'aggregateId',
            'eventOccurredOn',
            'eventMetadata',
            'authorizerId',
            'sourceEventId',
        ];

        foreach ($propertyNamesAndValues as $name => $value) {
            if (in_array($name, $skipProperties, true)) {
                continue;
            }

            $payload[$name] = $value;
        }

        return $payload;
    }

    /**
     * @throws EventException\NoEventClassMappingFound
     * @throws EventException\PropertyIsOfInvalidType
     * @throws EventException\ArraysNotAllowedWhenMappingPayload
     * @throws EventException\JsonEventEncodingError
     * @throws EventException\NoEventReflectionClassMappingMethodFound
     * @throws EventException\EventMappingReflectionError
     */
    private static function eventToSerializedEvent(Event $event): SerializedEvent
    {
        $occurredOn = $event->eventOccurredOn();
        $occurredOn = $occurredOn->setTimezone(new \DateTimeZone('UTC'));

        return SerializedEvent::fromProperties(
            $event->eventId()->id(),
            $event->aggregateId()->id(),
            is_null($event->authorizerId()) ? null : $event->authorizerId()->id(),
            is_null($event->sourceEventId()) ? null : $event->sourceEventId()->id(),
            $occurredOn->format('Y-m-d H:i:s.u'),
            self::eventClassToEventName(get_class($event)),
            PayloadMapper::arrayPayloadToJsonPayload(
                self::arrayPayloadFromEvent($event),
                // Allowing arrays would encourage CRUDy events.
                // It would also encourage big aggregates. E.g. a Group aggregate containing Member entities.
                // Instead, that should be modelled with Group aggregate, and Member aggregates,
                // where the membership is stored in the Member aggregate.
                false
            ),
            PayloadMapper::arrayPayloadToJsonPayload(
                $event->eventMetadata(),
                true
            )
        );
    }

    // SECTION - EVENT TO JSON EVENT

    /**
     * @param Event[] $events
     *
     * @return SerializedEvent[]
     *
     * @throws EventException\NoEventClassMappingFound
     * @throws EventException\PropertyIsOfInvalidType
     * @throws EventException\ArraysNotAllowedWhenMappingPayload
     * @throws EventException\JsonEventEncodingError
     * @throws EventException\NoEventReflectionClassMappingMethodFound
     * @throws EventException\EventMappingReflectionError
     */
    public static function eventsToSerializedEvents(array $events): array
    {
        return array_map(
            function (Event $event): SerializedEvent {
                return self::eventToSerializedEvent($event);
            },
            $events
        );
    }

    /**
     * @throws EventException\JsonEventEncodingError
     * @throws EventException\NoEventClassMappingFound
     * @throws EventException\PropertyIsOfInvalidType
     * @throws EventException\ArraysNotAllowedWhenMappingPayload
     * @throws EventException\NoEventReflectionClassMappingMethodFound
     * @throws EventException\EventMappingReflectionError
     */
    private static function eventToJsonEvent(Event $event): string
    {
        $occurredOn = $event->eventOccurredOn();
        $occurredOn = $occurredOn->setTimezone(new \DateTimeZone('UTC'));

        $return = [
            'eventId' => $event->eventId()->id(),
            'aggregateId' => $event->aggregateId()->id(),
            'authorizerId' => is_null($event->authorizerId()) ? null : $event->authorizerId()->id(),
            'sourceEventId' => is_null($event->sourceEventId()) ? null : $event->sourceEventId()->id(),
            'eventOccurredOn' => $occurredOn->format('Y-m-d H:i:s.u'),
            'eventName' => self::eventClassToEventName(get_class($event)),
            'payload' => json_decode(
                PayloadMapper::arrayPayloadToJsonPayload(
                    self::arrayPayloadFromEvent($event),
                    // Allowing arrays would encourage CRUDy events.
                    // It would also encourage big aggregates. E.g. a Group aggregate containing Member entities.
                    // Instead, that should be modelled with Group aggregate, and Member aggregates,
                    // where the membership is stored in the Member aggregate.
                    // Also, some thought was given to allowing commands that have object structure.
                    // E.g. PushMessage with a Restrictions field that takes an object that has either the schema for
                    // RestrictionsSchemaA or RestrictionsSchemaB, as to make impossible states impossible.
                    // If that is ever needed, events, endpoints, and commands should be split up.
                    false
                ),
                true
            ),
            'metadata' => json_decode(
                PayloadMapper::arrayPayloadToJsonPayload(
                    $event->eventMetadata(),
                    true
                ),
                true
            ),
        ];

        // json decoding with associative arrays means that {} becomes an array
        // either figure out handling payloads and metadata with stdClass instead of array internally (possible todo)
        // or leave as is, and create this fix here, such that when transforming events to json
        // empty payloads and empty metadata becomes an empty object.
        if ([] === $return['payload']) {
            $return['payload'] = new \stdClass();
        }

        if ([] === $return['metadata']) {
            $return['metadata'] = new \stdClass();
        }

        $return = json_encode($return);

        if (is_string($return)) {
            return $return;
        }

        throw new EventException\JsonEventEncodingError('Could not encode');
    }

    /**
     * @param Event[] $events
     *
     * @return string[]
     *
     * @throws EventException\JsonEventEncodingError
     * @throws EventException\NoEventClassMappingFound
     * @throws EventException\PropertyIsOfInvalidType
     * @throws EventException\ConflictingAggregateIds
     * @throws EventException\RepeatedEventIds
     * @throws EventException\ArraysNotAllowedWhenMappingPayload
     * @throws EventException\NoEventReflectionClassMappingMethodFound
     * @throws EventException\EventMappingReflectionError
     */
    public static function eventsToJsonEvents(array $events): array
    {
        return array_map(
            function (Event $event): string {
                return self::eventToJsonEvent($event);
            },
            $events
        );
    }

    // SECTION - AGGREGATE FROM EVENTS

    /**
     * @param Event[] $events
     *
     * @throws EventException\ConflictingAggregateIds|EventException\RepeatedEventIds
     */
    private static function checkEventsForInconsistencies(array $events): void
    {
        $aggregateId = null;
        $eventIds = [];

        foreach ($events as $event) {
            if (null === $aggregateId) {
                $aggregateId = $event->aggregateId()->id();
            }
            if (
                null !== $aggregateId &&
                $aggregateId !== $event->aggregateId()->id()
            ) {
                throw new EventException\ConflictingAggregateIds(sprintf('Conflicting ids: "%s" and "%s"', $aggregateId, $event->aggregateId()->id()));
            }
            $eventId = $event->eventId()->id();
            if (array_key_exists($eventId, $eventIds)) {
                throw new EventException\RepeatedEventIds('Repeated the following id: '.$eventId);
            }
            $eventIds[$eventId] = $eventId;
        }
    }

    /**
     * @param Event[] $transformationEvents
     *
     * @throws EventException\NoCreationMappingFound
     * @throws EventException\NoTransformationMappingFound
     * @throws EventException\ConflictingAggregateIds
     * @throws EventException\RepeatedEventIds
     */
    public static function aggregateFromEvents(
        Event $creationEvent,
        array $transformationEvents
    ): Aggregate {
        self::checkEventsForInconsistencies(array_merge([$creationEvent], $transformationEvents));

        if ($creationEvent instanceof User\Event\EventCreatedUser) {
            $aggregate = $creationEvent->createUser();
        } elseif ($creationEvent instanceof Session\Event\EventCreatedSession) {
            $aggregate = $creationEvent->createSession();
        } else {
            throw new EventException\NoCreationMappingFound('No mapping found for: '.get_class($creationEvent));
        }

        foreach ($transformationEvents as $transformationEvent) {
            if (
                $transformationEvent instanceof User\Event\EventTransformedUser &&
                $aggregate instanceof User\Aggregate\User
            ) {
                $aggregate = $transformationEvent->transformUser($aggregate);
            } elseif (
                $transformationEvent instanceof Session\Event\EventTransformedSession &&
                $aggregate instanceof Session\Aggregate\Session
            ) {
                $aggregate = $transformationEvent->transformSession($aggregate);
            }  else {
                throw new EventException\NoTransformationMappingFound('No mapping found for: '.get_class($transformationEvent));
            }
        }

        return $aggregate;
    }

    /**
     * @return string[]
     *
     * @throws EventException\EventMappingReflectionError
     */
    public static function listAllEventNames(): array
    {
        static::setup();

        return array_values(self::$eventClassesToEventNames);
    }
}

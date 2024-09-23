<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

use Galeas\Api\Common\Event\Exception\ArraysNotAllowedWhenMappingPayload;
use Galeas\Api\Common\Event\Exception as EventException;
use Galeas\Api\Common\Event\Exception\JsonEventEncodingError;
use Galeas\Api\Common\Event\Exception\PropertyIsOfInvalidType;
use Galeas\Api\Common\Id\Id;

abstract class EventSerializer extends EventReflectionBaseClass
{
    /**
     * @param Event[] $events
     *
     * @return SerializedEvent[]
     *
     * @throws EventException\NoEventClassMappingFound
     * @throws PropertyIsOfInvalidType
     * @throws ArraysNotAllowedWhenMappingPayload
     * @throws JsonEventEncodingError
     * @throws EventException\EventMappingReflectionError
     */
    public static function eventsToSerializedEvents(array $events): array
    {
        return array_map(
            static fn (Event $event): SerializedEvent => self::eventToSerializedEvent($event),
            $events
        );
    }

    /**
     * @throws EventException\NoEventClassMappingFound
     * @throws PropertyIsOfInvalidType
     * @throws ArraysNotAllowedWhenMappingPayload
     * @throws JsonEventEncodingError
     * @throws EventException\EventMappingReflectionError
     */
    private static function eventToSerializedEvent(Event $event): SerializedEvent
    {
        // This converts the time to whatever it is at UTC, so that
        // recordedOn is saved in UTC format.
        $recordedOn = $event->recordedOn();
        $recordedOn = $recordedOn->setTimezone(new \DateTimeZone('UTC'));

        return SerializedEvent::fromProperties(
            $event->eventId()->id(),
            $event->aggregateId()->id(),
            $event->aggregateVersion(),
            $event->causationId()->id(),
            $event->correlationId()->id(),
            $recordedOn->format('Y-m-d H:i:s.u e'),
            self::eventClassToEventName($event::class),
            self::arrayPayloadToJsonPayload(
                self::arrayPayloadFromEvent($event),
                // Allowing arrays would encourage CRUDy events.
                // It would also encourage big aggregates. E.g. a Group aggregate containing Member entities.
                // Instead, that should be modelled with Group aggregate, and Member aggregates,
                // where the membership is stored in the Member aggregate.
                false
            ),
            self::arrayPayloadToJsonPayload(
                $event->metadata(),
                true
            )
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function arrayPayloadFromEvent(Event $event): array
    {
        $properties = (new \ReflectionObject($event))
            ->getProperties(
                \ReflectionProperty::IS_PUBLIC + \ReflectionProperty::IS_PROTECTED + \ReflectionProperty::IS_PRIVATE
            )
        ;

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
            'causationId',
            'aggregateVersion',
            'eventVersion',
            'correlationId',
            'recordedOn',
            'metadata',
        ];

        foreach ($propertyNamesAndValues as $name => $value) {
            if (\in_array($name, $skipProperties, true)) {
                continue;
            }

            $payload[$name] = $value;
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $arrayPayload
     *
     * @throws ArraysNotAllowedWhenMappingPayload|JsonEventEncodingError|PropertyIsOfInvalidType
     */
    private static function arrayPayloadToJsonPayload(array $arrayPayload, bool $arrayPropertiesAllowed): string
    {
        if ([] === $arrayPayload) {
            return '{}';
        }

        $return = json_encode(
            self::arrayPayloadToSerializedArrayPayload(
                $arrayPayload,
                $arrayPropertiesAllowed
            )
        );

        if (\is_string($return)) {
            return $return;
        }

        throw new JsonEventEncodingError('Error in arrayPayloadToJsonPayload');
    }

    /**
     * @param array<string, mixed> $arrayPayload
     *
     * @return array<string, mixed>
     *
     * @throws ArraysNotAllowedWhenMappingPayload|PropertyIsOfInvalidType
     */
    private static function arrayPayloadToSerializedArrayPayload(array $arrayPayload, bool $arrayPropertiesAllowed): array
    {
        $payload = [];

        foreach ($arrayPayload as $propertyName => $value) {
            if (
                (!\is_array($value))
                && (!\is_string($value))
                && (null !== $value)
                && (!\is_bool($value))
                && (!\is_int($value))
                && (!\is_float($value))
                && (!$value instanceof Id)
                && (!$value instanceof \DateTimeImmutable)
            ) {
                throw new PropertyIsOfInvalidType(\sprintf('Property %s is a %s, instead it should be one of: string, null, boolean, integer, float, \DateTimeImmutable, ..\Id\Id', $propertyName, \gettype($value)));
            }

            if (
                \is_array($value)
                && $arrayPropertiesAllowed
            ) {
                $value = self::arrayPayloadToSerializedArrayPayload($value, $arrayPropertiesAllowed);
            }

            if (
                \is_array($value)
                && (!$arrayPropertiesAllowed)
            ) {
                throw new ArraysNotAllowedWhenMappingPayload();
            }

            if ($value instanceof \DateTimeImmutable) {
                $value = [
                    'type' => 'payload_datetime',
                    'datetime' => $value->format('Y-m-d H:i:s.u'),
                    'timezone' => $value->getTimezone()->getName(),
                ];
            }

            if ($value instanceof Id) {
                $value = [
                    'type' => 'payload_id',
                    'id' => $value->id(),
                ];
            }

            $payload[$propertyName] = $value;
        }

        return $payload;
    }
}

<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

use Galeas\Api\Common\Event\Exception as EventException;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;

abstract class EventDeserializer extends EventReflectionBaseClass
{
    /**
     * @param SerializedEvent[] $serializedEvents
     *
     * @return Event[]
     *
     * @throws EventException\UnrecoverableDeserializationError
     * @throws EventException\EventMappingReflectionError
     * @throws EventException\NoEventReflectionClassMappingMethodFound
     * @throws InvalidId
     */
    public static function serializedEventsToEvents(array $serializedEvents): array
    {
        return array_map(
            static fn (SerializedEvent $serializedEvent): Event => self::serializedEventToEvent($serializedEvent),
            $serializedEvents
        );
    }

    /**
     * @return array<string,mixed>
     *
     * @throws EventException\UnrecoverableDeserializationError
     */
    public static function jsonPayloadToArrayPayload(string $jsonPayload, string $eventName, bool $trueIfJsonPayloadFalseIfMetadata): array
    {
        /** @var null|array<string, mixed> $arrayPayload */
        $arrayPayload = json_decode(
            $jsonPayload,
            true
        );
        if (null === $arrayPayload) {
            throw new EventException\UnrecoverableDeserializationError('Could not recover for: '.$jsonPayload);
        }

        return self::serializedArrayPayloadToArrayPayload(
            $arrayPayload,
            $eventName,
            $trueIfJsonPayloadFalseIfMetadata
        );
    }

    /**
     * @throws InvalidId
     * @throws EventException\UnrecoverableDeserializationError
     * @throws EventException\EventMappingReflectionError
     * @throws EventException\NoEventReflectionClassMappingMethodFound
     */
    private static function serializedEventToEvent(SerializedEvent $serializedEvent): Event
    {
        $reflectionClassMethod = self::eventNameToReflectionClassMethod($serializedEvent->eventName());

        try {
            $event = $reflectionClassMethod->invoke(
                null,
                Id::fromId($serializedEvent->eventId()),
                Id::fromId($serializedEvent->aggregateId()),
                $serializedEvent->aggregateVersion(),
                Id::fromId($serializedEvent->causationId()),
                Id::fromId($serializedEvent->correlationId()),
                \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u e', $serializedEvent->recordedOn()),
                self::jsonPayloadToArrayPayload($serializedEvent->jsonMetadata(), $serializedEvent->eventName(), false),
                self::jsonPayloadToArrayPayload($serializedEvent->jsonPayload(), $serializedEvent->eventName(), true)
            );
        } catch (\ReflectionException $exception) {
            throw new EventException\EventMappingReflectionError('Reflection method failure. Inside method.');
        }

        if (!$event instanceof Event) {
            throw new EventException\EventMappingReflectionError('Reflection method failure. No event generated.');
        }

        return $event;
    }

    /**
     * @param array<string,mixed> $serializedArrayPayload
     *
     * @return array<string,mixed>
     *
     * @throws EventException\UnrecoverableDeserializationError
     */
    private static function serializedArrayPayloadToArrayPayload(array $serializedArrayPayload, string $eventName, bool $trueIfJsonPayloadFalseIfMetadata): array
    {
        $payload = [];

        try {
            foreach ($serializedArrayPayload as $propertyName => $value) {
                $reflectionClass = self::eventNameToReflectionClass($eventName);
                if ($trueIfJsonPayloadFalseIfMetadata) {
                    $type = $reflectionClass->getProperty($propertyName)->getType();
                    if (
                        $type instanceof \ReflectionType
                        && Id::class === $type->getName()
                    ) {
                        $value = Id::fromId($value);
                    }
                }

                $payload[$propertyName] = $value;
            }
        } catch (\Throwable $exception) {
            $jsonPayload = json_encode($serializedArrayPayload);
            $jsonPayload = false === $jsonPayload ? 'Could not encode failed payload' : $jsonPayload;

            throw new EventException\UnrecoverableDeserializationError(
                \sprintf(
                    'Unrecoverable error in deserialization of payload: %s, exception %s, message: %s',
                    $jsonPayload,
                    $exception::class,
                    $exception->getMessage()
                )
            );
        }

        return $payload;
    }
}

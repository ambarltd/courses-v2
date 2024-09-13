<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

use Galeas\Api\Common\Id\Id;

trait EventWithPresetEventIdNoAuthorizerAndSourceTrait
{
    /**
     * @var Id
     */
    private $eventId;

    /**
     * @var Id
     */
    private $aggregateId;

    /**
     * @var null
     */
    private $authorizerId;

    /**
     * @var Id
     */
    private $sourceEventId;

    /**
     * @var \DateTimeImmutable
     */
    private $eventOccurredOn;

    /**
     * @var array
     */
    private $eventMetadata;

    private function __construct(
        Id $eventId,
        Id $aggregateId,
        Id $sourceEventId,
        array $eventMetadata
    ) {
        $this->eventId = $eventId;
        $this->eventOccurredOn = new \DateTimeImmutable('now');
        $this->aggregateId = $aggregateId;
        $this->authorizerId = null;
        $this->sourceEventId = $sourceEventId;
        $this->eventMetadata = $eventMetadata;
    }

    /**
     * TODO
     * @throws \RuntimeException
     */
    private static function reflectionConstructor(
        Id $eventId,
        \DateTimeImmutable $eventOccurredOn,
        Id $aggregateId,
        ?Id $authorizerId,
        ?Id $sourceEventId,
        array $eventMetadata,
        array $extraPropertiesAndValues
    ): self {
        if (null === $authorizerId) {
            throw new \RuntimeException('AuthorizerId cannot be null for this type of event');
        }
        if (null !== $sourceEventId) {
            throw new \RuntimeException('SourceEventId cannot be set for this type of event');
        }

        $event = new self(
            $aggregateId,
            $authorizerId,
            $eventMetadata
        );
        $event->sourceEventId = $sourceEventId;
        $event->eventOccurredOn = $eventOccurredOn;
        $event->eventId = $eventId;

        foreach ($extraPropertiesAndValues as $property => $value) {
            $event->$property = $value;
        }

        return $event;
    }

    public function eventId(): Id
    {
        return $this->eventId;
    }

    public function aggregateId(): Id
    {
        return $this->aggregateId;
    }

    public function authorizerId()
    {
        return $this->authorizerId;
    }

    /**
     * @return Id
     */
    public function sourceEventId()
    {
        return $this->sourceEventId;
    }

    public function eventOccurredOn(): \DateTimeImmutable
    {
        return $this->eventOccurredOn;
    }

    public function eventMetadata(): array
    {
        return $this->eventMetadata;
    }
}

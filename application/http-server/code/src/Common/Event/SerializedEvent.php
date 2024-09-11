<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

class SerializedEvent
{
    /**
     * @var string
     */
    private $eventId;

    /**
     * @var string
     */
    private $aggregateId;

    /**
     * @var string|null
     */
    private $authorizerId;

    /**
     * @var string|null
     */
    private $sourceEventId;

    /**
     * @var string
     */
    private $eventOccurredOn;

    /**
     * @var string
     */
    private $eventName;

    /**
     * @var string
     */
    private $jsonPayload;

    /**
     * @var string
     */
    private $jsonMetadata;

    private function __construct(
        string $eventId,
        string $aggregateId,
        ?string $authorizerId,
        ?string $sourceEventId,
        string $eventOccurredOn,
        string $eventName,
        string $jsonPayload,
        string $jsonMetadata
    ) {
        $this->eventId = $eventId;
        $this->aggregateId = $aggregateId;
        $this->authorizerId = $authorizerId;
        $this->sourceEventId = $sourceEventId;
        $this->eventOccurredOn = $eventOccurredOn;
        $this->eventName = $eventName;
        $this->jsonPayload = $jsonPayload;
        $this->jsonMetadata = $jsonMetadata;
    }

    public function eventId(): string
    {
        return $this->eventId;
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * @return string
     */
    public function authorizerId(): ?string
    {
        return $this->authorizerId;
    }

    /**
     * @return string
     */
    public function sourceEventId(): ?string
    {
        return $this->sourceEventId;
    }

    public function eventOccurredOn(): string
    {
        return $this->eventOccurredOn;
    }

    public function eventName(): string
    {
        return $this->eventName;
    }

    public function jsonPayload(): string
    {
        return $this->jsonPayload;
    }

    public function jsonMetadata(): string
    {
        return $this->jsonMetadata;
    }

    /**
     * @return SerializedEvent
     */
    public static function fromProperties(
        string $eventId,
        string $aggregateId,
        ?string $authorizerId,
        ?string $sourceEventId,
        string $eventOccurredOn,
        string $eventName,
        string $jsonPayload,
        string $jsonMetadata
    ): self {
        return new self(
            $eventId,
            $aggregateId,
            $authorizerId,
            $sourceEventId,
            $eventOccurredOn,
            $eventName,
            $jsonPayload,
            $jsonMetadata
        );
    }
}

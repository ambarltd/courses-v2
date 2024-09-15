<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

class SerializedEvent
{
    private string $eventId;

    private string $aggregateId;

    private int $aggregateVersion;

    private string $causationId;

    private string $correlationId;

    private string $recordedOn;

    private string $eventName;

    private string $jsonPayload;

    private string $jsonMetadata;

    private function __construct(
        string $eventId,
        string $aggregateId,
        int $aggregateVersion,
        ?string $causationId,
        ?string $correlationId,
        string $recordedOn,
        string $eventName,
        string $jsonPayload,
        string $jsonMetadata
    ) {
        $this->eventId = $eventId;
        $this->aggregateId = $aggregateId;
        $this->aggregateVersion = $aggregateVersion;
        $this->causationId = $causationId;
        $this->correlationId = $correlationId;
        $this->recordedOn = $recordedOn;
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

    public function aggregateVersion(): int
    {
        return $this->aggregateVersion;
    }

    public function causationId(): string
    {
        return $this->causationId;
    }

    public function correlationId(): string
    {
        return $this->correlationId;
    }

    public function recordedOn(): string
    {
        return $this->recordedOn;
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

    public static function fromProperties(
        string $eventId,
        string $aggregateId,
        int $aggregateVersion,
        string $causationId,
        string $correlationId,
        string $recordedOn,
        string $eventName,
        string $jsonPayload,
        string $jsonMetadata
    ): self {
        return new self(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $eventName,
            $jsonPayload,
            $jsonMetadata
        );
    }
}

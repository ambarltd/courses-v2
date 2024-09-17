<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

use Galeas\Api\Common\Id\Id;

trait EventTrait
{
    private Id $eventId;

    private Id $aggregateId;

    private int $aggregateVersion;

    private Id $causationId;

    private Id $correlationId;

    private \DateTimeImmutable $recordedOn;

    private array $metadata;

    private function __construct(
        Id $eventId,
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId,
        \DateTimeImmutable $recordedOn,
        array $metadata
    ) {
        $this->eventId = $eventId;
        $this->aggregateId = $aggregateId;
        $this->aggregateVersion = $aggregateVersion;
        $this->causationId = $causationId;
        $this->correlationId = $correlationId;
        $this->recordedOn = $recordedOn;
        $this->metadata = $metadata;
    }

    /**
     * @throws \RuntimeException
     */
    private static function reflectionConstructor(
        Id $eventId,
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId,
        \DateTimeImmutable $recordedOn,
        array $metadata,
        array $extraPropertiesAndValues
    ): self {
        $event = new self(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata
        );

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

    public function aggregateVersion(): int
    {
        return $this->aggregateVersion;
    }

    public function causationId(): Id
    {
        return $this->causationId;
    }

    public function correlationId(): Id
    {
        return $this->correlationId;
    }

    public function recordedOn(): \DateTimeImmutable
    {
        return $this->recordedOn;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }
}

<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

use Galeas\Api\Common\Event\Exception\FoundBadJsonForSerializedEvent;

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
        string $causationId,
        string $correlationId,
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

    /**
     * @throws FoundBadJsonForSerializedEvent
     */
    public function toJson(): string
    {
        $json = json_encode([
            'eventId' => $this->eventId,
            'aggregateId' => $this->aggregateId,
            'aggregateVersion' => $this->aggregateVersion,
            'causationId' => $this->causationId,
            'correlationId' => $this->correlationId,
            'recordedOn' => $this->recordedOn,
            'eventName' => $this->eventName,
            'jsonPayload' => json_decode($this->jsonPayload, false),
            'jsonMetadata' => json_decode($this->jsonMetadata, false),
        ]);

        if (!\is_string($json)) {
            throw new FoundBadJsonForSerializedEvent();
        }

        return $json;
    }

    /**
     * @throws FoundBadJsonForSerializedEvent
     */
    public static function fromJson(string $json): self
    {
        $jsonObject = json_decode($json);

        if (
            \is_object($jsonObject)
            && isset($jsonObject->eventId, $jsonObject->aggregateId, $jsonObject->aggregateVersion, $jsonObject->causationId, $jsonObject->correlationId, $jsonObject->recordedOn, $jsonObject->eventName, $jsonObject->jsonPayload, $jsonObject->jsonMetadata)
            && \is_string($jsonObject->eventId)
            && \is_string($jsonObject->aggregateId)
            && \is_int($jsonObject->aggregateVersion)
            && \is_string($jsonObject->causationId)
            && \is_string($jsonObject->correlationId)
            && \is_string($jsonObject->recordedOn)
            && \is_string($jsonObject->eventName)
            && \is_object($jsonObject->jsonPayload)
            && \is_object($jsonObject->jsonMetadata)
        ) {
            $payload = json_encode($jsonObject->jsonPayload);
            $metadata = json_encode($jsonObject->jsonMetadata);
            if (!\is_string($payload) || !\is_string($metadata)) {
                throw new FoundBadJsonForSerializedEvent();
            }

            return new self(
                $jsonObject->eventId,
                $jsonObject->aggregateId,
                $jsonObject->aggregateVersion,
                $jsonObject->causationId,
                $jsonObject->correlationId,
                $jsonObject->recordedOn,
                $jsonObject->eventName,
                $payload,
                $metadata,
            );
        }

        throw new FoundBadJsonForSerializedEvent();
    }

    /**
     * @throws FoundBadJsonForSerializedEvent
     */
    public static function fromAmbarJson(string $json): self
    {
        $json = json_decode($json, true);

        if (
            \is_array($json)
            && \array_key_exists('event_id', $json)
            && \array_key_exists('aggregate_id', $json)
            && \array_key_exists('aggregate_version', $json)
            && \array_key_exists('causation_id', $json)
            && \array_key_exists('correlation_id', $json)
            && \array_key_exists('recorded_on', $json)
            && \array_key_exists('event_name', $json)
            && \array_key_exists('json_payload', $json)
            && \array_key_exists('json_metadata', $json)
            && \is_string($json['event_id'])
            && \is_string($json['aggregate_id'])
            && \is_int($json['aggregate_version'])
            && \is_string($json['causation_id'])
            && \is_string($json['correlation_id'])
            && \is_string($json['recorded_on'])
            && \is_string($json['event_name'])
            && \is_string($json['json_payload'])
            && \is_string($json['json_metadata'])
        ) {
            return new self(
                $json['event_id'],
                $json['aggregate_id'],
                $json['aggregate_version'],
                $json['causation_id'],
                $json['correlation_id'],
                $json['recorded_on'],
                $json['event_name'],
                $json['json_payload'],
                $json['json_metadata'],
            );
        }

        throw new FoundBadJsonForSerializedEvent();
    }
}

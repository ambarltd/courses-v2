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

    public function toJson(): string {
        return json_encode([
            "eventId" => $this->eventId,
            "aggregateId" => $this->aggregateId,
            "aggregateVersion" => $this->aggregateVersion,
            "causationId" => $this->causationId,
            "correlationId" => $this->correlationId,
            "recordedOn" => $this->recordedOn,
            "eventName" => $this->eventName,
            "jsonPayload" => json_decode($this->jsonPayload, true),
            "jsonMetadata" => json_decode($this->jsonMetadata, true),
        ]);
    }

    public static function fromJson(string $json): self {
        $json = json_decode($json, true);

        if (
            array_key_exists("eventId", $json) &&
            array_key_exists("aggregateId", $json) &&
            array_key_exists("aggregateVersion", $json) &&
            array_key_exists("causationId", $json) &&
            array_key_exists("correlationId", $json) &&
            array_key_exists("recordedOn", $json) &&
            array_key_exists("eventName", $json) &&
            array_key_exists("jsonPayload", $json) &&
            array_key_exists("jsonMetadata", $json) &&
            is_string($json["eventId"]) &&
            is_string($json["aggregateId"]) &&
            is_int($json["aggregateVersion"]) &&
            is_string($json["causationId"]) &&
            is_string($json["correlationId"]) &&
            is_string($json["recordedOn"]) &&
            is_string($json["eventName"]) &&
            is_array($json["jsonPayload"]) &&
            is_array($json["jsonMetadata"])
        ) {
            return new self(
                $json["eventId"],
                $json["aggregateId"],
                $json["aggregateVersion"],
                $json["causationId"],
                $json["correlationId"],
                $json["recordedOn"],
                $json["eventName"],
                json_encode($json["jsonPayload"]),
                json_encode($json["jsonMetadata"]),
            );
        }

        throw new FoundBadJsonForSerializedEvent();
    }

    public static function fromAmbarJson(string $json): self {
        $json = json_decode($json, true);

        if (
            array_key_exists("event_id", $json) &&
            array_key_exists("aggregate_id", $json) &&
            array_key_exists("aggregate_version", $json) &&
            array_key_exists("causation_id", $json) &&
            array_key_exists("correlation_id", $json) &&
            array_key_exists("recorded_on", $json) &&
            array_key_exists("event_name", $json) &&
            array_key_exists("json_payload", $json) &&
            array_key_exists("json_metadata", $json) &&
            is_string($json["event_id"]) &&
            is_string($json["aggregate_id"]) &&
            is_int($json["aggregate_version"]) &&
            is_string($json["causation_id"]) &&
            is_string($json["correlation_id"]) &&
            is_string($json["recorded_on"]) &&
            is_string($json["event_name"]) &&
            is_string($json["json_payload"]) &&
            is_string($json["json_metadata"])
        ) {
            return new self(
                $json["event_id"],
                $json["aggregate_id"],
                $json["aggregate_version"],
                $json["causation_id"],
                $json["correlation_id"],
                $json["recorded_on"],
                $json["event_name"],
                $json["json_payload"],
                $json["json_metadata"],
            );
        }

        throw new FoundBadJsonForSerializedEvent();
    }
}

using System.Text.Json.Serialization;

namespace CreditCardEnrollment.Common.Events;

public class SerializedEvent
{
    [JsonPropertyName("id")]
    public int? Id { get; init; }

    [JsonPropertyName("event_id")]
    public required string EventId { get; init; }

    [JsonPropertyName("aggregate_id")]
    public required string AggregateId { get; init; }

    [JsonPropertyName("causation_id")]
    public required string CausationId { get; init; }

    [JsonPropertyName("correlation_id")]
    public required string CorrelationId { get; init; }

    [JsonPropertyName("aggregate_version")]
    public int AggregateVersion { get; init; }

    [JsonPropertyName("json_payload")]
    public required string JsonPayload { get; init; }

    [JsonPropertyName("json_metadata")]
    public string? JsonMetadata { get; init; }

    [JsonPropertyName("recorded_on")]
    public required string RecordedOn { get; init; }

    [JsonPropertyName("event_name")]
    public required string EventName { get; init; }
}

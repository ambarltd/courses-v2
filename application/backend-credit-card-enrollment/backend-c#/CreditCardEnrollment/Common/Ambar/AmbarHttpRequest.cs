using System.Text.Json.Serialization;
using CreditCardEnrollment.Common.Events;

namespace CreditCardEnrollment.Common.Ambar;

public class AmbarHttpRequest
{
    [JsonPropertyName("data_source_id")]
    public required string DataSourceId { get; init; }
    
    [JsonPropertyName("data_source_description")]
    public required string DataSourceDescription { get; init; }
    
    [JsonPropertyName("data_destination_id")]
    public required string DataDestinationId { get; init; }
    
    [JsonPropertyName("data_destination_description")]
    public required string DataDestinationDescription { get; init; }
    
    [JsonPropertyName("payload")]
    public required SerializedEvent SerializedEvent { get; init; }
}

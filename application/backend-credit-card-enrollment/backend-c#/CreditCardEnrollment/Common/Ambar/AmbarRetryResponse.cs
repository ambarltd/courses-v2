using System.Text.Json.Serialization;

namespace CreditCardEnrollment.Common.Ambar;

public class AmbarRetryResponse(Exception exception)
{
    [JsonPropertyName("status")]
    public string Status { get; set; } = string.Empty;

    [JsonPropertyName("error")]
    public string? Error { get; set; }

    [JsonPropertyName("stackTrace")]
    public string? StackTrace { get; set; }
    
    public static AmbarResponse RetryResponse(Exception ex) => new()
    {
        Status = "retry",
        Error = ex.Message,
        StackTrace = ex.StackTrace
    };
}
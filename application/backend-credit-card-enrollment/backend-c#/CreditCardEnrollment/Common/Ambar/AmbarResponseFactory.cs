using System.Text.Json.Serialization;

namespace CreditCardEnrollment.Common.Ambar;

public class AmbarResponse
{
    [JsonPropertyName("status")]
    public string Status { get; set; } = string.Empty;

    [JsonPropertyName("error")]
    public string? Error { get; set; }

    [JsonPropertyName("stackTrace")]
    public string? StackTrace { get; set; }
}

public static class AmbarResponseFactory
{
    public static AmbarResponse SuccessResponse() => new()
    {
        Status = "success",
        Error = "{}"
    };

    public static AmbarResponse RetryResponse(Exception ex) => new()
    {
        Status = "retry",
        Error = ex.Message,
        StackTrace = ex.StackTrace
    };
}

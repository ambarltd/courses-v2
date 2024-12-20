using System.Text.Json.Serialization;

namespace CreditCardEnrollment.Common.Ambar;

public class AmbarSuccessResponse
{
    [JsonPropertyName("result")] 
    public Result Result { get; } = new Result();
}

public class Result
{
    [JsonPropertyName("success")] 
    public Success Success { get; } = new Success();
}

public class Success;
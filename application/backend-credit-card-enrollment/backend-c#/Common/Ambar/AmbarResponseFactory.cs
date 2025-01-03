namespace CreditCardEnrollment.Common.Ambar;

public static class AmbarResponseFactory
{
    public static string RetryResponse(Exception exception)
    {
        var message = exception.Message.Replace("\"", "\\\"");
        return $"{{\"result\":{{\"error\":{{\"policy\":\"must_retry\",\"class\":\"{exception.GetType()}\",\"description\":\"message:{message}\"}}}}}}";
    }

    public static string SuccessResponse()
    {
        return "{\"result\":{\"success\":{}}}";
    }
}
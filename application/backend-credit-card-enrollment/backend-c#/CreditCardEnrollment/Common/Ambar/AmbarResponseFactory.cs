namespace CreditCardEnrollment.Common.Ambar;

public static class AmbarResponseFactory
{
    public static string SuccessResponse() => "{\"status\":\"success\"}";

    public static string RetryResponse(Exception ex) => 
        $"{{\"status\":\"retry\",\"error\":\"{ex.Message.Replace("\"", "\\\"")}\",\"stackTrace\":\"{ex.StackTrace?.Replace("\"", "\\\"")}\"}}";}

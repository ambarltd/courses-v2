package cloud.ambar.common.ambar;

public class AmbarResponseFactory {
    public static String retryResponse(Exception exception) {
        String message = exception.getMessage() != null ? exception.getMessage() : "";
        message = message.replace("\"", "\\\"");
        return "{\"result\":{\"error\":{\"policy\":\"MUST_RETRY\",\"class\":\"" + exception.getClass() + "\",\"description\":\"message:" + message + "\"}}}";
    }

    public static String successResponse() {
        return "{\"result\":{\"success\":{}}}";
    }
}

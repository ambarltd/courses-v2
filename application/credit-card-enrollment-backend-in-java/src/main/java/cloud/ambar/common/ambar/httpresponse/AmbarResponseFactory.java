package cloud.ambar.common.ambar.httpresponse;

public class AmbarResponseFactory {
    public static String retryResponse(String err) {
        return "{\"result\":{\"error\":{\"policy\":\"MUST_RETRY\",\"class\":\"UnexpectedException\",\"description\":\"" + err + "\"}}}";
    }

    public static String successResponse() {
        return "{\"result\":{\"success\":{}}}";
    }
}

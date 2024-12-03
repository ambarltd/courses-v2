package cloud.ambar.common.ambar.httpresponse;

import java.util.Arrays;

public class AmbarResponseFactory {
    public static String retryResponse(Exception exception) {
        return "{\"result\":{\"error\":{\"policy\":\"MUST_RETRY\",\"class\":\"" + exception.getClass() + "\",\"description\":\"message:" + exception.getMessage() + "\"}}}";
    }

    public static String successResponse() {
        return "{\"result\":{\"success\":{}}}";
    }
}

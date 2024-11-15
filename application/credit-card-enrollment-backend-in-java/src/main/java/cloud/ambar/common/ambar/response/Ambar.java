package cloud.ambar.common.ambar.response;

public class Ambar {
    public static AmbarResponse retryResponse(String err) {
        return AmbarResponse.builder()
                .result(Result.builder()
                        .error(Error.builder()
                                .policy(ErrorPolicy.MUST_RETRY.toString())
                                .errorClass("UnexpectedException")
                                .description(err)
                                .build())
                        .build())
                .build();
    }

    public static AmbarResponse keepGoingResponse(String err) {
        return AmbarResponse.builder()
                .result(Result.builder()
                        .error(Error.builder()
                                .policy(ErrorPolicy.KEEP_GOING.toString())
                                .errorClass("UnexpectedEventException")
                                .description(err)
                                .build())
                        .build())
                .build();
    }

    public static AmbarResponse successResponse() {
        return AmbarResponse.builder()
                .result(Result.builder()
                        .success(new Success())
                        .build())
                .build();
    }
}

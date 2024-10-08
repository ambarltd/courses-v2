package cloud.ambar.common.ambar;

import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@NoArgsConstructor
public class Success {

    @JsonProperty("result")
    private Result result = new Result();

    @Data
    @NoArgsConstructor
    public static class Result {

        @JsonProperty("success")
        private SuccessContent success = new SuccessContent();

        @Data
        @NoArgsConstructor
        public static class SuccessContent {
            // Empty content for success
        }
    }
}


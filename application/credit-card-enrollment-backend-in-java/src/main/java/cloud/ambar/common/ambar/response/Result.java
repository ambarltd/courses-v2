package cloud.ambar.common.ambar.response;

import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class Result {
    @JsonProperty("error")
    private Error error;
    @JsonProperty("success")
    private Success success;
}

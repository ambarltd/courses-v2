package cloud.ambar.creditCardProduct.projection.models.response;

import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class Error {
    @JsonProperty("policy")
    private String policy;

    @JsonProperty("class")
    private String errorClass;

    @JsonProperty("description")
    private String description;
}

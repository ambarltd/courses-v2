package cloud.ambar.creditCardProduct.data.models.ambar;

import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class AmbarResponse {
    @JsonProperty("result")
    private Result result;
}

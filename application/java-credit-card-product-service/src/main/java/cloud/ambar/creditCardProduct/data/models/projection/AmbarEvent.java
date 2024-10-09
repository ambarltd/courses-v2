package cloud.ambar.creditCardProduct.data.models.projection;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@NoArgsConstructor
@AllArgsConstructor
@JsonIgnoreProperties(ignoreUnknown = true)
public class AmbarEvent {
    @JsonProperty("data_source_id")
    private String dataSourceId;
    @JsonProperty("data_source_description")
    private String dataSourceDescription;
    @JsonProperty("data_destination_id")
    private String dataDestinationId;
    @JsonProperty("data_destination_description")
    private String dataDestinationDescription;
    @JsonProperty("payload")
    private String payload;
}

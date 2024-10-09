package cloud.ambar.common.ambar;

import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.databind.JsonNode;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@NoArgsConstructor
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
    private JsonNode payload;
}

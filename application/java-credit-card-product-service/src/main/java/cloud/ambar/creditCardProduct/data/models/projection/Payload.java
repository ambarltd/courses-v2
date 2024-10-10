package cloud.ambar.creditCardProduct.data.models.projection;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@NoArgsConstructor
@JsonIgnoreProperties(ignoreUnknown = true)
public class Payload {
    @JsonProperty("id")
    private Long id;

    @JsonProperty("event_id")
    private String eventId;

    @JsonProperty("aggregate_id")
    private String aggregateId;

    @JsonProperty("causation_id")
    private String causationID;

    @JsonProperty("correlation_id")
    private String correlationId;

    @JsonProperty("aggregate_version")
    private long version;

    @JsonProperty("json_payload")
    private String data;

    @JsonProperty("event_name")
    private String eventName;
}
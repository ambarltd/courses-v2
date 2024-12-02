package cloud.ambar.common.ambar.httprequest;

import cloud.ambar.common.serializedevent.SerializedEvent;
import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.NonNull;

@Data
@NoArgsConstructor
public class AmbarHttpRequest {
    @JsonProperty("data_source_id")
    @NonNull private String dataSourceId;
    @JsonProperty("data_source_description")
    @NonNull private String dataSourceDescription;
    @JsonProperty("data_destination_id")
    @NonNull private String dataDestinationId;
    @JsonProperty("data_destination_description")
    @NonNull private String dataDestinationDescription;
    @JsonProperty("payload")
    @NonNull private SerializedEvent serializedEvent;
}

package cloud.ambar.common.projection;

import lombok.Builder;
import lombok.Getter;
import lombok.NonNull;
import lombok.Setter;

@Builder
@Setter
@Getter
public class ProjectedEvent {
    @NonNull private String eventId;
    @NonNull private String projectionName;
}
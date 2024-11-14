package cloud.ambar.common.projection;

import cloud.ambar.common.ambar.event.Payload;
import com.fasterxml.jackson.core.JsonProcessingException;

public interface Projector {
    void project(final Payload event) throws JsonProcessingException;
}

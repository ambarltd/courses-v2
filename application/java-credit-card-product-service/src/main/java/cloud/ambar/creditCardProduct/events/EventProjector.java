package cloud.ambar.creditCardProduct.events;

import cloud.ambar.creditCardProduct.data.models.projection.Payload;
import com.fasterxml.jackson.core.JsonProcessingException;

public interface EventProjector {
    public void project(Payload eventPayload) throws JsonProcessingException;
}

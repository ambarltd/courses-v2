package cloud.ambar.creditCardProduct.events;

import cloud.ambar.common.models.Event;
import cloud.ambar.creditCardProduct.data.models.projection.Payload;

public interface EventProjector {
    public void project(Payload eventPayload);
}

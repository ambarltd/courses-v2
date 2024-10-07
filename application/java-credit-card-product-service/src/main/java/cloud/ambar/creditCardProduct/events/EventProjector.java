package cloud.ambar.creditCardProduct.events;

import cloud.ambar.common.models.Event;

public interface EventProjector {
    public void project(Event event);
}

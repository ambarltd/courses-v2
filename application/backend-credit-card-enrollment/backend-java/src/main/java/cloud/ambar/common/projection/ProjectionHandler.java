package cloud.ambar.common.projection;

import cloud.ambar.common.event.Event;

public abstract class ProjectionHandler {
    public abstract void project(final Event event);
}

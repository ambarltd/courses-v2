package cloud.ambar.common.projection;

import cloud.ambar.common.event.Event;

public abstract class ProjectionHandler {
    protected abstract void project(final Event event);
}

package cloud.ambar.common.projection;

import cloud.ambar.common.event.Event;
import com.fasterxml.jackson.core.JsonProcessingException;

public abstract class ProjectionHandler {
    protected abstract void project(final Event event) throws JsonProcessingException;
}

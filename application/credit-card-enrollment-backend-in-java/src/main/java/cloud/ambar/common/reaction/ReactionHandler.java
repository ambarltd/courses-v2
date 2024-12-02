package cloud.ambar.common.reaction;

import cloud.ambar.common.event.Event;
import com.fasterxml.jackson.core.JsonProcessingException;

public abstract class ReactionHandler {
    protected abstract void react(final Event event) throws JsonProcessingException;
}

package cloud.ambar.common.reaction;

import cloud.ambar.common.event.Event;
import cloud.ambar.common.eventstore.PostgresTransactionalEventStore;
import com.fasterxml.jackson.core.JsonProcessingException;
import lombok.RequiredArgsConstructor;

@RequiredArgsConstructor
public abstract class ReactionHandler {
    final protected PostgresTransactionalEventStore postgresTransactionalEventStore;
    protected abstract void react(final Event event) throws JsonProcessingException;
}

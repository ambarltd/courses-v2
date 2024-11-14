package cloud.ambar.common.reaction;

import cloud.ambar.common.ambar.event.Payload;
import cloud.ambar.common.event.Event;
import cloud.ambar.common.event.store.EventRepository;
import cloud.ambar.product.enrollment.aggregate.EnrollmentAggregate;
import com.fasterxml.jackson.core.JsonProcessingException;

import java.util.List;
import java.util.UUID;

public abstract class Reactor {
    protected abstract void react(final Payload event) throws JsonProcessingException;

    protected EnrollmentAggregate hydrateAggregateForId(EventRepository eventStore, String id) {
        final List<Event> enrollmentEvents = eventStore.findAllByAggregateId(id);
        final EnrollmentAggregate aggregate = new EnrollmentAggregate(id);
        if (enrollmentEvents.isEmpty()) {
            throw new RuntimeException();
        }

        for (Event event: enrollmentEvents) {
            aggregate.apply(event);
        }
        return aggregate;
    }

    protected String deterministicEventId(final String causationId) {
        return UUID.nameUUIDFromBytes((causationId + "REACTION").getBytes()).toString();
    }
}

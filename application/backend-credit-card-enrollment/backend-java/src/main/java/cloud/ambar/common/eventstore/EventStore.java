package cloud.ambar.common.eventstore;

import cloud.ambar.common.aggregate.Aggregate;
import cloud.ambar.common.event.CreationEvent;
import cloud.ambar.common.event.Event;
import cloud.ambar.common.event.TransformationEvent;
import cloud.ambar.common.serializedevent.Deserializer;
import cloud.ambar.common.serializedevent.SerializedEvent;
import cloud.ambar.common.serializedevent.Serializer;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.util.List;

@Service
@RequestScope
@RequiredArgsConstructor
public class EventStore {
    private final EventRepository eventRepository;
    private final Deserializer deserializer;
    private final Serializer serializer;
    private boolean transactionActive = false;

    public AggregateAndEventIdsInLastEvent findAggregate(String aggregateId) {
        if (!transactionActive) {
            throw new RuntimeException("Transaction must be active to read aggregate from event store!");
        }
        final List<SerializedEvent> serializedEvents = eventRepository.findAllByAggregateId(aggregateId);
        final List<Event> events = serializedEvents.stream()
                .map(deserializer::deserialize)
                .toList();

        if (events.isEmpty()) {
            throw new RuntimeException("No events found for aggregateId: " + aggregateId);
        }

        final Event creationEvent = events.getFirst();
        final List<Event> transformationEvents = events.subList(1, events.size());

        Aggregate aggregate;
        if (creationEvent instanceof CreationEvent<?>) {
            aggregate = ((CreationEvent<Aggregate>) creationEvent).createAggregate();
        } else {
            throw new RuntimeException("First event is not a creation event");
        }

        String eventIdOfLastEvent = creationEvent.getEventId();
        String correlationIdOfLastEvent = creationEvent.getCorrelationId();

        for (Event transformationEvent : transformationEvents) {
            if (transformationEvent instanceof TransformationEvent<?>) {
               aggregate = ((TransformationEvent<Aggregate>) transformationEvent).transformAggregate(aggregate);
               eventIdOfLastEvent = transformationEvent.getEventId();
               correlationIdOfLastEvent = transformationEvent.getCorrelationId();

            } else {
                throw new RuntimeException("Event is not a transformation event");
            }
        }


        return new AggregateAndEventIdsInLastEvent(
                aggregate,
                eventIdOfLastEvent,
                correlationIdOfLastEvent
        );
    }

    public void saveEvent(Event event) {
        if (!transactionActive) {
            throw new RuntimeException("Transaction must be active to write into event store!");
        }
        eventRepository.save(serializer.serialize(event));
    }

    public boolean doesEventAlreadyExist(String eventId) {
        if (!transactionActive) {
            throw new RuntimeException("Transaction must be active to read event from event store!");
        }
        return eventRepository.findByEventId(eventId).isPresent();
    }

    public void beginTransaction() {
        if (transactionActive) {
            throw new RuntimeException("Transaction already active.");
        }
        // todo transaction begin
        transactionActive = true;
    }

    public void commitTransaction() {
        if (!transactionActive) {
            throw new RuntimeException("No transaction to commit.");
        }

        // todo transaction commit
        transactionActive = false;
    }

    public void abortTransaction() {
        if (!transactionActive) {
            throw new RuntimeException("No transaction to abort");
        }

        // todo abort transaction
        transactionActive = false;
    }

    public void closeSession() {
        // todo close session
    }

    public boolean isTransactionActive() {
        return transactionActive;
    }
}

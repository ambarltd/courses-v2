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

import java.util.List;

@Service
@RequiredArgsConstructor
public class EventStore {
    private final EventRepository eventRepository;
    private final Deserializer deserializer;
    private final Serializer serializer;

    public boolean doesEventAlreadyExist(String eventId) {
        return eventRepository.findByEventId(eventId).isPresent();
    }

    public AggregateAndEventIdsInLastEvent findAggregate(String aggregateId) {
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
        // todo transactional
        eventRepository.save(serializer.serialize(event));
    }
}

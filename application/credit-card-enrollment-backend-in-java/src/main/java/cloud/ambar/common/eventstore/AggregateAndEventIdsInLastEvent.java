package cloud.ambar.common.eventstore;

import cloud.ambar.common.aggregate.Aggregate;
import lombok.Getter;

@Getter
public class AggregateAndEventIdsInLastEvent {
    private final Aggregate aggregate;
    private final String eventIdOfLastEvent;
    private final String correlationIdOfLastEvent;

    public AggregateAndEventIdsInLastEvent(Aggregate aggregate, String eventIdOfLastEvent, String correlationIdOfLastEvent) {
        this.aggregate = aggregate;
        this.eventIdOfLastEvent = eventIdOfLastEvent;
        this.correlationIdOfLastEvent = correlationIdOfLastEvent;
    }
}

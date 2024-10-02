package cloud.ambar.common.models;

import cloud.ambar.common.exceptions.InvalidEventException;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.NoArgsConstructor;

import java.util.List;
import java.util.Objects;

public abstract class AggregateTraits implements Aggregate {

    private String aggregateId;
    private long aggregateVersion;

    public AggregateTraits(String aggregateId, long aggregateVersion) {
        this.aggregateId = aggregateId;
        this.aggregateVersion = aggregateVersion;
    }

    public void load(final List<Event> events) {
        events.forEach(event -> {
            this.validateEvent(event);
            this.raiseEvent(event);
            this.aggregateVersion++;
        });
    }
    public void raiseEvent(final Event event) {
        this.validateEvent(event);
        transform(event);

        this.aggregateVersion++;
    }

    private void validateEvent(final Event event) {
        if (Objects.isNull(event) || !event.getAggregateId().equals(this.aggregateId))
            throw new InvalidEventException(event.toString());
    }
}

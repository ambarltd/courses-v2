package cloud.ambar.common.models;

import cloud.ambar.common.exceptions.InvalidEventException;
import lombok.Getter;

import java.util.List;
import java.util.Objects;

@Getter
public abstract class AggregateTraits implements Aggregate {

    private final String aggregateId;
    private long aggregateVersion;

    public AggregateTraits(String aggregateId, long aggregateVersion) {
        this.aggregateId = aggregateId;
        this.aggregateVersion = aggregateVersion;
    }

    public void apply(final Event event) {
        this.validateEvent(event);
        transform(event);

        this.aggregateVersion++;
    }

    private void validateEvent(final Event event) {
        if (Objects.isNull(event) || !event.getAggregateId().equals(this.aggregateId))
            throw new InvalidEventException(event.toString());
    }
}

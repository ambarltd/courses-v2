package cloud.ambar.creditCardProduct.aggregate;

import cloud.ambar.creditCardProduct.exceptions.InvalidEventException;
import cloud.ambar.creditCardProduct.events.Event;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.util.Objects;

@Data
@NoArgsConstructor
public abstract class AggregateTraits implements Aggregate {

    private String aggregateId;
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

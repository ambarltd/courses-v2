package cloud.ambar.common.aggregate;

import cloud.ambar.common.event.Event;
import lombok.Data;
import lombok.NoArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import java.util.Objects;

@Data
@NoArgsConstructor
public abstract class Aggregate implements IAggregate {
    private static final Logger log = LogManager.getLogger(Aggregate.class);

    private String aggregateId;
    private long aggregateVersion;

    public Aggregate(String aggregateId) {
        this.aggregateId = aggregateId;
        this.aggregateVersion = 0;
    }

    public void apply(final Event event) {
        log.info("Applying Event: " + event);
        this.validateEvent(event);
        transform(event);

        this.aggregateVersion++;
    }

    private void validateEvent(final Event event) {
        log.info("Validating Event: " + event);
        if (Objects.isNull(event) || !event.getAggregateId().equals(this.aggregateId))
            throw new RuntimeException();
    }
}

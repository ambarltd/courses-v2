package cloud.ambar.creditCardProduct.common.models;

import cloud.ambar.creditCardProduct.common.exceptions.InvalidEventException;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.util.ArrayList;
import java.util.List;
import java.util.Objects;

@Data
@NoArgsConstructor
public abstract class Aggregate {

    protected String id;
    protected String type;
    protected long version;
    protected final List<Event> changes = new ArrayList<>();

    public Aggregate(final String id, final String aggregateType) {
        this.id = id;
        this.type = aggregateType;
    }


    public abstract void when(final Event event);

    public void load(final List<Event> events) {
        events.forEach(event -> {
            this.validateEvent(event);
            this.raiseEvent(event);
            this.version++;
        });
    }

    public void apply(final Event event) {
        this.validateEvent(event);
        event.setAggregateType(this.type);

        when(event);
        changes.add(event);

        this.version++;
        event.setVersion(this.version);
    }

    public void raiseEvent(final Event event) {
        this.validateEvent(event);

        event.setAggregateType(this.type);
        when(event);

        this.version++;
    }

    public void clearChanges() {
        this.changes.clear();
    }

    private void validateEvent(final Event event) {
        if (Objects.isNull(event) || !event.getAggregateId().equals(this.id))
            throw new InvalidEventException(event.toString());
    }
}

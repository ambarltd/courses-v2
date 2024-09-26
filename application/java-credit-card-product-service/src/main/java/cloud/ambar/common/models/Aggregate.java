package cloud.ambar.common.models;

import cloud.ambar.common.exceptions.InvalidEventException;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.util.ArrayList;
import java.util.List;
import java.util.Objects;

@Data
@NoArgsConstructor
public abstract class Aggregate {

    protected String id;
    protected long version;
    // TBD if this is needed?
    protected final List<Event> changes = new ArrayList<>();

    public Aggregate(final String id) {
        this.id = id;
    }

    public abstract void transform(final Event event);

    public void load(final List<Event> events) {
        events.forEach(event -> {
            this.validateEvent(event);
            this.raiseEvent(event);
            this.version++;
        });
    }
    public void raiseEvent(final Event event) {
        this.validateEvent(event);
        transform(event);

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

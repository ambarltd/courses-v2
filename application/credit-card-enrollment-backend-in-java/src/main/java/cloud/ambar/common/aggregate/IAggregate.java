package cloud.ambar.common.aggregate;

import cloud.ambar.common.event.Event;

public interface IAggregate {
    void transform(final Event event);
}

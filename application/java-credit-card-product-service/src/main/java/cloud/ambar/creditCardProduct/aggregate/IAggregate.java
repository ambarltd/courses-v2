package cloud.ambar.creditCardProduct.aggregate;

import cloud.ambar.creditCardProduct.events.Event;

public interface IAggregate {
    void transform(final Event event);
}

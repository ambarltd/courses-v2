package cloud.ambar.creditCardProduct.aggregate;

import cloud.ambar.creditCardProduct.events.Event;

public interface Aggregate {
    void transform(final Event event);
}

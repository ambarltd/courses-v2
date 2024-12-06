package cloud.ambar.common.event;

import lombok.experimental.SuperBuilder;

@SuperBuilder
public abstract class CreationEvent<A> extends Event {
    public abstract A createAggregate();
}

package cloud.ambar.common.event;

import lombok.experimental.SuperBuilder;

@SuperBuilder
public abstract class TransformationEvent<A> extends Event {
    public abstract A transformAggregate(A aggregate);
}

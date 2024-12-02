package cloud.ambar.common.event;

import lombok.Getter;
import lombok.NonNull;
import lombok.experimental.SuperBuilder;

import java.time.Instant;

@SuperBuilder
@Getter
public abstract class Event {
    @NonNull protected String eventId;

    @NonNull protected Integer aggregateVersion;

    @NonNull protected String aggregateId;

    @NonNull protected String causationId;

    @NonNull protected String correlationId;

    @NonNull protected Instant recordedOn;
}

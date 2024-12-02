package cloud.ambar.common.aggregate;

import lombok.Getter;
import lombok.NonNull;
import lombok.experimental.SuperBuilder;

@Getter
@SuperBuilder(toBuilder = true)
public abstract class Aggregate {
    @NonNull protected String aggregateId;
    @NonNull protected Integer aggregateVersion;
}

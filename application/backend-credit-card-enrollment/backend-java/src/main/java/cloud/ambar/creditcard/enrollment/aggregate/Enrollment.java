package cloud.ambar.creditcard.enrollment.aggregate;

import cloud.ambar.common.aggregate.Aggregate;
import lombok.Getter;
import lombok.NonNull;
import lombok.experimental.SuperBuilder;

import java.time.Instant;

@SuperBuilder(toBuilder = true)
@Getter
public class Enrollment extends Aggregate {
    @NonNull private String userId;
    @NonNull private String productId;
    @NonNull private String status;
    @NonNull private Integer annualIncomeInCents;
    @NonNull private Instant enrollmentFirstRequestedOn;
}

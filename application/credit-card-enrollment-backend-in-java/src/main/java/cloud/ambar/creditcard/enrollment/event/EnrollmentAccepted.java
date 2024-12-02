package cloud.ambar.creditcard.enrollment.event;

import cloud.ambar.common.event.TransformationEvent;
import cloud.ambar.creditcard.enrollment.aggregate.Enrollment;
import cloud.ambar.creditcard.enrollment.aggregate.EnrollmentStatus;
import lombok.Getter;
import lombok.NonNull;
import lombok.experimental.SuperBuilder;

@SuperBuilder
@Getter
public class EnrollmentAccepted extends TransformationEvent<Enrollment> {
    @NonNull private String reasonCode;
    @NonNull private String reasonDescription;

    public Enrollment transformAggregate(Enrollment aggregate) {
        return aggregate.toBuilder()
                .aggregateVersion(aggregateVersion)
                .status(EnrollmentStatus.ACCEPTED.name())
                .build();
    }
}

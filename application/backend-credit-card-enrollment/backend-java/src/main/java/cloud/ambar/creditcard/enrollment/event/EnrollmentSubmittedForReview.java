package cloud.ambar.creditcard.enrollment.event;

import cloud.ambar.common.event.TransformationEvent;
import cloud.ambar.creditcard.enrollment.aggregate.Enrollment;
import cloud.ambar.creditcard.enrollment.aggregate.EnrollmentStatus;
import lombok.Getter;
import lombok.experimental.SuperBuilder;

@SuperBuilder
@Getter
public class EnrollmentSubmittedForReview extends TransformationEvent<Enrollment> {
    public Enrollment transformAggregate(Enrollment aggregate) {
        return aggregate.toBuilder()
                .aggregateVersion(aggregateVersion)
                .status(EnrollmentStatus.SUBMITTED_FOR_REVIEW.name())
                .build();
    }
}

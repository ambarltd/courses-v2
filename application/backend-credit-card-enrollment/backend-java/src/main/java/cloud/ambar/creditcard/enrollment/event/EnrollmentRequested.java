package cloud.ambar.creditcard.enrollment.event;

import cloud.ambar.common.event.CreationEvent;
import cloud.ambar.creditcard.enrollment.aggregate.Enrollment;
import cloud.ambar.creditcard.enrollment.aggregate.EnrollmentStatus;
import lombok.Getter;
import lombok.NonNull;
import lombok.experimental.SuperBuilder;

@SuperBuilder
@Getter
public class EnrollmentRequested extends CreationEvent<Enrollment> {
    @NonNull private String userId;
    @NonNull private String productId;
    @NonNull private Integer annualIncomeInCents;

    public Enrollment createAggregate() {
        return Enrollment.builder()
                .aggregateId(aggregateId)
                .aggregateVersion(aggregateVersion)
                .userId(userId)
                .productId(productId)
                .enrollmentFirstRequestedOn(recordedOn)
                .annualIncomeInCents(annualIncomeInCents)
                .status(EnrollmentStatus.Requested.name())
                .build();
    }
}
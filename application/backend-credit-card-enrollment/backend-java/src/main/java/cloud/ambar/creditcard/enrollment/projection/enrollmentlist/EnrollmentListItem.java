package cloud.ambar.creditcard.enrollment.projection.enrollmentlist;

import lombok.*;

import java.time.Instant;

@Builder
@Getter
public class EnrollmentListItem {
    @NonNull private String id;
    @NonNull private String userId;
    @NonNull private String productId;
    @NonNull private String productName;
    @NonNull private Instant requestedDate;
    @NonNull private String status;
    private String statusReason;
    private Instant reviewedOn;
}
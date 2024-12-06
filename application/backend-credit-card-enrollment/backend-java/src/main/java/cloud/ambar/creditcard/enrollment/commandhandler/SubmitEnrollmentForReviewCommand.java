package cloud.ambar.creditcard.enrollment.commandhandler;

import lombok.Builder;
import lombok.Getter;
import lombok.NonNull;

@Builder
@Getter
public class SubmitEnrollmentForReviewCommand {
    @NonNull private String userId;
    @NonNull private String enrollmentId;
}

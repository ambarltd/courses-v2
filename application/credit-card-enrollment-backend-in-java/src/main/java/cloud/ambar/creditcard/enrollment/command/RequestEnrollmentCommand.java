package cloud.ambar.creditcard.enrollment.command;

import lombok.Builder;
import lombok.Getter;
import lombok.NonNull;

@Builder
@Getter
public class RequestEnrollmentCommand {
    @NonNull private String userId;
    @NonNull private String productId;
    @NonNull private Integer annualIncome;
}

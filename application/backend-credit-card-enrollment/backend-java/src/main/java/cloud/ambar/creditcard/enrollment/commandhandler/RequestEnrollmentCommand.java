package cloud.ambar.creditcard.enrollment.commandhandler;

import cloud.ambar.common.commandhandler.Command;
import lombok.Builder;
import lombok.Getter;
import lombok.NonNull;

@Builder
@Getter
public class RequestEnrollmentCommand extends Command {
    @NonNull private String userId;
    @NonNull private String productId;
    @NonNull private Integer annualIncomeInCents;
}

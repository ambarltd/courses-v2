package cloud.ambar.creditcard.enrollment.commandhandler;

import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Positive;
import lombok.Data;

@Data
public class RequestEnrollmentHttpRequest {
    @NotNull private String productId;
    @Positive private int annualIncomeInCents;
}

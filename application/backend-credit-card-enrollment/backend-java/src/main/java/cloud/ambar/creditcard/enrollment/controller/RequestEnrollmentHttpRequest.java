package cloud.ambar.creditcard.enrollment.controller;

import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Positive;
import lombok.Data;

@Data
public class RequestEnrollmentHttpRequest {
    @NotNull private String productId;
    @Positive private int annualIncomeInCents;
}

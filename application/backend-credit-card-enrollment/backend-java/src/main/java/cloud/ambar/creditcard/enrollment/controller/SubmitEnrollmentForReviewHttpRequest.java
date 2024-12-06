package cloud.ambar.creditcard.enrollment.controller;

import jakarta.validation.constraints.NotNull;
import lombok.Data;

@Data
public class SubmitEnrollmentForReviewHttpRequest {
    @NotNull
    private String enrollmentId;
}

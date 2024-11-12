package cloud.ambar.product.enrollment.controllers.requests;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class EnrollmentRequest {
    // What they want access too (aggregateId of the credit card product)
    private String productId;
    // Something we use for decisions.
    private int annualIncome;
}

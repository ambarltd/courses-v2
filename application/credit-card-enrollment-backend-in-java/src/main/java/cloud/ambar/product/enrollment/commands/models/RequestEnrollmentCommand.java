package cloud.ambar.product.enrollment.commands.models;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class RequestEnrollmentCommand {
    // Who is requesting the enrollment (aggregateId of the user)
    private String userId;
    // What they want access too (aggregateId of the credit card product)
    private String productId;
    // Something we use for decisions.
    private int annualIncome;
}

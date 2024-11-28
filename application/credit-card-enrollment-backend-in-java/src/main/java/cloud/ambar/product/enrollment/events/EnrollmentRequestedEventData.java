package cloud.ambar.product.enrollment.events;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
@JsonIgnoreProperties(ignoreUnknown = true)
public class EnrollmentRequestedEventData {
    public static final String EVENT_NAME = "CreditCardProduct_Product_EnrollmentRequested";

    // Who is requesting the enrollment (aggregateId of the user)
    private String userId;
    // What they want access too (aggregateId of the credit card product)
    private String productId;
    // Something we use for decisions.
    private int annualIncome;
}
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
public class EnrollmentDeclinedEventData {
    public static final String EVENT_NAME = "CreditCardProduct_Product_EnrollmentDeclined";

    private String reasonCode;
    private String reasonDescription;
}

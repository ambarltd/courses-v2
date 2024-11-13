package cloud.ambar.product.enrollment.events;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@AllArgsConstructor
@NoArgsConstructor
@JsonIgnoreProperties(ignoreUnknown = true)
public class EnrollmentAcceptedEventData {
    public static final String EVENT_NAME = "CreditCardProduct_Product_EnrollmentAccepted";
    private String id;
}

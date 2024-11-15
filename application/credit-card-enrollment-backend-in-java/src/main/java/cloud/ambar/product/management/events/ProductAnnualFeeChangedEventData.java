package cloud.ambar.product.management.events;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ProductAnnualFeeChangedEventData {
    public static final String EVENT_NAME = "CreditCardProduct_Product_AnnualFeeChanged";
    private int annualFeeInCents;
}

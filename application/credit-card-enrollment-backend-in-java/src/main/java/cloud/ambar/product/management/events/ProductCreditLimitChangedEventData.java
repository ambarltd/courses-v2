package cloud.ambar.product.management.events;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ProductCreditLimitChangedEventData {
    public static final String EVENT_NAME = "CreditCardProduct_Product_CreditLimitChanged";
    private int creditLimitInCents;
}

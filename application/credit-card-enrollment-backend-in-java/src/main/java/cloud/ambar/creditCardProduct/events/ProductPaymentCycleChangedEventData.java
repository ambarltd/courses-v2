package cloud.ambar.creditCardProduct.events;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ProductPaymentCycleChangedEventData {
    public static final String EVENT_NAME = "CreditCardProduct_Product_PaymentCycleChanged";
    private String paymentCycle;
}

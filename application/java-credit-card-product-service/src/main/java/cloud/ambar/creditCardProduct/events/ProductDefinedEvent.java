package cloud.ambar.creditCardProduct.events;

import lombok.Builder;
import lombok.Data;


@Data
@Builder
public class ProductDefinedEvent {
    public static final String EVENT_NAME = "CreditCardProduct_Product_ProductDefined";


    // Below are the product details for the event, to be returned as the serialized data of the event
    private String name;
    private int interestInBasisPoints;
    private int annualFeeInCents;
    private String paymentCycle;
    private int creditLimitInCents;
    private int maxBalanceTransferAllowedInCents;
    private String reward;
    private String cardBackgroundHex;
}

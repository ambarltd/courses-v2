package cloud.ambar.creditCardProduct.events;

import cloud.ambar.common.models.Event;
import cloud.ambar.creditCardProduct.aggregate.IEventTransformProduct;
import cloud.ambar.creditCardProduct.aggregate.ProductAggregate;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;


@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
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

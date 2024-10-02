package cloud.ambar.creditCardProduct.events;

import cloud.ambar.common.models.Event;
import cloud.ambar.creditCardProduct.aggregate.IEventTransformProduct;
import cloud.ambar.creditCardProduct.aggregate.ProductAggregate;

public class ProductActivatedEvent extends Event implements IEventTransformProduct {
    public static final String EVENT_NAME = "CreditCardProduct_Product_ProductActivated";

    private String productIdentifierForAggregateIdHash;
    private String name;
    private int interestInBasisPoints;
    private int annualFeeInCents;
    private String paymentCycle;
    private int creditLimitInCents;
    private int maxBalanceTransferAllowedInCents;
    private String reward;
    private String cardBackgroundHex;
    @Override
    public ProductAggregate transformProduct(ProductAggregate productAggregate) {
        return null;
    }
}

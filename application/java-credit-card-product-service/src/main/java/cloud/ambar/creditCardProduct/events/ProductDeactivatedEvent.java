package cloud.ambar.creditCardProduct.events;

import cloud.ambar.common.models.Event;
import cloud.ambar.creditCardProduct.aggregate.IEventTransformProduct;
import cloud.ambar.creditCardProduct.aggregate.ProductAggregate;

public class ProductDeactivatedEvent extends Event implements IEventTransformProduct {
    public static final String EVENT_NAME = "CreditCardProduct_Product_ProductDeactivated";
    @Override
    public ProductAggregate transformProduct(ProductAggregate productAggregate) {
        return null;
    }
}

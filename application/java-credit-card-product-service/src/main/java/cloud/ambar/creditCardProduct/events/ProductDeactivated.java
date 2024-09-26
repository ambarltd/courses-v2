package cloud.ambar.creditCardProduct.events;

import cloud.ambar.common.models.Event;
import cloud.ambar.creditCardProduct.aggregate.IEventTransformProduct;
import cloud.ambar.creditCardProduct.aggregate.Product;

public class ProductDeactivated extends Event implements IEventTransformProduct {
    public static final String EVENT_TYPE = "ProductDeactivated";
    @Override
    public Product transformProduct(Product product) {
        return null;
    }
}

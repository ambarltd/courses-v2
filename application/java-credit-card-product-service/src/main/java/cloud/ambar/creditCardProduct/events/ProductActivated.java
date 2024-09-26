package cloud.ambar.creditCardProduct.events;

import cloud.ambar.common.models.Event;
import cloud.ambar.creditCardProduct.aggregate.IEventTransformProduct;
import cloud.ambar.creditCardProduct.aggregate.Product;

public class ProductActivated extends Event implements IEventTransformProduct {
    public static final String EVENT_TYPE = "ProductActivated";
    @Override
    public Product transformProduct(Product product) {
        return product;
    }
}

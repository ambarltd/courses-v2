package cloud.ambar.creditCardProduct.aggregate;

import cloud.ambar.common.models.Aggregate;
import cloud.ambar.common.models.Event;
import cloud.ambar.creditCardProduct.events.ProductActivated;
import cloud.ambar.creditCardProduct.events.ProductDeactivated;
import cloud.ambar.creditCardProduct.events.ProductDefined;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

public class Product extends Aggregate {
    private static final Logger log = LogManager.getLogger(Product.class);
    @Override
    public void transform(Event event) {
        final String type = event.getEventType();
        switch (type) {
            case ProductDefined.EVENT_TYPE:
                log.info("ProductDefined Event!");
                return;
            case ProductActivated.EVENT_TYPE:
                log.info("ProductActivated Event!");
                return;
            case ProductDeactivated.EVENT_TYPE:
                log.info("ProductDeactivated Event!");
                return;
            default:
                log.info("Event unrelated to Product: " + type);
        }
    }
}

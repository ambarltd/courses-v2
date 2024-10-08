package cloud.ambar.creditCardProduct.events.projection;

import cloud.ambar.common.models.Event;
import cloud.ambar.creditCardProduct.data.mongo.ProjectionRepository;
import cloud.ambar.creditCardProduct.events.EventProjector;
import cloud.ambar.creditCardProduct.events.ProductActivatedEvent;
import cloud.ambar.creditCardProduct.events.ProductDeactivatedEvent;
import cloud.ambar.creditCardProduct.events.ProductDefinedEvent;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;

/**
 * Takes aggregates and projects them into a list of products for querying later.
 */
@Service
public class ProductProjectorService implements EventProjector {
    private static final Logger log = LogManager.getLogger(ProductProjectorService.class);

    private final ProjectionRepository projectionRepository;

    public ProductProjectorService(final ProjectionRepository projectionRepository) {
        this.projectionRepository = projectionRepository;
    }

    @Override
    public void project(Event event) {
        switch (event.getEventName()) {
            case ProductDefinedEvent.EVENT_NAME -> {
                log.info("Handling projection for ProductDefinedEvent");
            }
            case ProductActivatedEvent.EVENT_NAME -> {
                log.info("Handling projection for ProductActivatedEvent");
            }
            case ProductDeactivatedEvent.EVENT_NAME -> {
                log.info("Handling projection for ProductDeactivatedEvent");
            }
        }
    }

}

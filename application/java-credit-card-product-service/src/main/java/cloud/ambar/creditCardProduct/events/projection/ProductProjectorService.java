package cloud.ambar.creditCardProduct.events.projection;

import cloud.ambar.common.models.Event;
import cloud.ambar.creditCardProduct.data.models.projection.Payload;
import cloud.ambar.creditCardProduct.data.mongo.ProjectionRepository;
import cloud.ambar.creditCardProduct.events.EventProjector;
import cloud.ambar.creditCardProduct.events.ProductActivatedEvent;
import cloud.ambar.creditCardProduct.events.ProductDeactivatedEvent;
import cloud.ambar.creditCardProduct.events.ProductDefinedEvent;
import cloud.ambar.creditCardProduct.data.models.projection.Product;
import com.fasterxml.jackson.databind.ObjectMapper;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;

import java.util.Optional;

/**
 * Takes aggregates and projects them into a list of products for querying later.
 */
@Service
public class ProductProjectorService implements EventProjector {
    private static final Logger log = LogManager.getLogger(ProductProjectorService.class);

    private final ProjectionRepository projectionRepository;

    private final ObjectMapper objectMapper;

    public ProductProjectorService(final ProjectionRepository projectionRepository) {
        this.projectionRepository = projectionRepository;
        this.objectMapper = new ObjectMapper();
    }

    @Override
    public void project(Payload event) {
        final Product product;
        switch (event.getEventName()) {
            case ProductDefinedEvent.EVENT_NAME -> {
                log.info("Handling projection for ProductDefinedEvent");
                product = objectMapper.convertValue(event.getData(), Product.class);
            }
            case ProductActivatedEvent.EVENT_NAME -> {
                log.info("Handling projection for ProductActivatedEvent");
                product = getProductOrThrow(event);
                product.setActive(true);
            }
            case ProductDeactivatedEvent.EVENT_NAME -> {
                log.info("Handling projection for ProductDeactivatedEvent");
                product = getProductOrThrow(event);
                product.setActive(false);
            }
            default -> {
                log.info("Event is not a ProductEvent, doing nothing...");
                return;
            }
        }

        projectionRepository.save(product);
    }

    private Product getProductOrThrow(Payload event) {
        Optional<Product> product = projectionRepository.findById(event.getAggregateId());
        if (product.isEmpty()) {
            final String msg = "Unable to find Product in projection repository for aggregate: " + event.getAggregateId();
            log.error(msg);
            throw new RuntimeException(msg);
        }
        return product.get();
    }

}

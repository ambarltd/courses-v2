package cloud.ambar.creditCardProduct.projection;

import cloud.ambar.creditCardProduct.projection.models.event.Payload;
import cloud.ambar.creditCardProduct.database.mongo.ProjectionRepository;
import cloud.ambar.creditCardProduct.events.ProductActivatedEventData;
import cloud.ambar.creditCardProduct.events.ProductDeactivatedEventData;
import cloud.ambar.creditCardProduct.events.ProductDefinedEventData;
import cloud.ambar.creditCardProduct.projection.models.CreditCardProduct;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;

import java.util.Optional;

/**
 * Takes aggregates and projects them into a list of products for querying later.
 */
@Service
public class CreditCardProductProjectionService {
    private static final Logger log = LogManager.getLogger(CreditCardProductProjectionService.class);

    private final ProjectionRepository projectionRepository;

    private final ObjectMapper objectMapper;

    public CreditCardProductProjectionService(final ProjectionRepository projectionRepository) {
        this.projectionRepository = projectionRepository;
        this.objectMapper = new ObjectMapper();
    }

    public void project(Payload event) throws JsonProcessingException {
        final CreditCardProduct creditCardProduct;
        switch (event.getEventName()) {
            case ProductDefinedEventData.EVENT_NAME -> {
                log.info("Handling projection for ProductDefinedEvent");
                creditCardProduct = objectMapper.readValue(event.getData(), CreditCardProduct.class);
                creditCardProduct.setId(event.getAggregateId());
            }
            case ProductActivatedEventData.EVENT_NAME -> {
                log.info("Handling projection for ProductActivatedEvent");
                creditCardProduct = getProductOrThrow(event);
                creditCardProduct.setActive(true);
            }
            case ProductDeactivatedEventData.EVENT_NAME -> {
                log.info("Handling projection for ProductDeactivatedEvent");
                creditCardProduct = getProductOrThrow(event);
                creditCardProduct.setActive(false);
            }
            default -> {
                log.info("Event is not a ProductEvent, doing nothing...");
                return;
            }
        }

        projectionRepository.save(creditCardProduct);
    }

    private CreditCardProduct getProductOrThrow(Payload event) {
        Optional<CreditCardProduct> product = projectionRepository.findById(event.getAggregateId());
        if (product.isEmpty()) {
            final String msg = "Unable to find Product in projection repository for aggregate: " + event.getAggregateId();
            log.error(msg);
            throw new RuntimeException(msg);
        }
        return product.get();
    }

}

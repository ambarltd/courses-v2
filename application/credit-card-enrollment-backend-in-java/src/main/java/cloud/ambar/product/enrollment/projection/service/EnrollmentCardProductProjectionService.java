package cloud.ambar.product.enrollment.projection.service;

import cloud.ambar.common.ambar.event.Payload;
import cloud.ambar.common.exceptions.UnexpectedEventException;
import cloud.ambar.common.projection.Projector;
import cloud.ambar.product.enrollment.projection.models.CardProduct;
import cloud.ambar.product.enrollment.projection.store.EnrollmentCardProductProjectionRepository;
import cloud.ambar.product.management.events.ProductActivatedEventData;
import cloud.ambar.product.management.events.ProductDeactivatedEventData;
import cloud.ambar.product.management.events.ProductDefinedEventData;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;

import java.util.Optional;

@Service
@RequiredArgsConstructor
public class EnrollmentCardProductProjectionService implements Projector {
    private static final Logger log = LogManager.getLogger(EnrollmentCardProductProjectionService.class);

    private final ObjectMapper om;

    private final EnrollmentCardProductProjectionRepository enrollmentCardProductProjectionRepository;

    @Override
    public void project(Payload event) throws JsonProcessingException {
        final CardProduct creditCardProduct;
        switch (event.getEventName()) {
            case ProductDefinedEventData.EVENT_NAME -> {
                log.info("Handling projection for ProductDefinedEvent");
                final ProductDefinedEventData eventData = om.readValue(event.getData(), ProductDefinedEventData.class);
                creditCardProduct = new CardProduct();
                creditCardProduct.setId(event.getAggregateId());
                creditCardProduct.setActive(false);
                creditCardProduct.setName(eventData.getName());
            }
            case ProductActivatedEventData.EVENT_NAME -> {
                log.info("Handling projection for ProductActivatedEvent");
                creditCardProduct = getProductOrThrow(event.getAggregateId());
                creditCardProduct.setActive(true);
            }
            case ProductDeactivatedEventData.EVENT_NAME -> {
                log.info("Handling projection for ProductDeactivatedEvent");
                creditCardProduct = getProductOrThrow(event.getAggregateId());
                creditCardProduct.setActive(false);
            }
            // For now Ambar is sending all events. But we could update the filter to only give us events related to
            // the properties of products which we actually display.
            // Throwing this will tell ambar to keep going despite something unexpected.
            default -> throw new UnexpectedEventException(event.getEventName());
        }

        enrollmentCardProductProjectionRepository.save(creditCardProduct);
    }

    private CardProduct getProductOrThrow(String id) {
        Optional<CardProduct> cardProduct = enrollmentCardProductProjectionRepository.findById(id);
        if (cardProduct.isEmpty()) {
            final String msg = "Unable to find CardProduct in projection repository for id: " + id;
            log.error(msg);
            throw new RuntimeException(msg);
        }
        return cardProduct.get();
    }
}

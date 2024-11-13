package cloud.ambar.product.management.projection;

import cloud.ambar.common.projection.Projector;
import cloud.ambar.product.management.events.ProductAnnualFeeChangedEventData;
import cloud.ambar.product.management.events.ProductBackgroundChangedEventData;
import cloud.ambar.common.exceptions.UnexpectedEventException;
import cloud.ambar.common.ambar.event.Payload;
import cloud.ambar.product.management.projection.store.CreditCardProductProjectionRepository;
import cloud.ambar.product.management.events.ProductActivatedEventData;
import cloud.ambar.product.management.events.ProductDeactivatedEventData;
import cloud.ambar.product.management.events.ProductDefinedEventData;
import cloud.ambar.product.management.projection.models.CreditCardProduct;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;

import java.util.Optional;

/**
 * Takes aggregates and projects them into a list of products for querying later.
 */
@Service
@RequiredArgsConstructor
public class ProductManagementProjectionService implements Projector {
    private static final Logger log = LogManager.getLogger(ProductManagementProjectionService.class);

    private final CreditCardProductProjectionRepository creditCardProductProjectionRepository;

    private final ObjectMapper objectMapper;

    public void project(Payload event) throws JsonProcessingException {
        final CreditCardProduct creditCardProduct;
        switch (event.getEventName()) {
            case ProductDefinedEventData.EVENT_NAME -> {
                log.info("Handling projection for ProductDefinedEvent");
                final ProductDefinedEventData data = objectMapper.readValue(event.getData(), ProductDefinedEventData.class);
                creditCardProduct = new CreditCardProduct();
                creditCardProduct.setId(event.getAggregateId());
                creditCardProduct.setName(data.getName());
                creditCardProduct.setActive(false);
                creditCardProduct.setRewardType(data.getReward());
                creditCardProduct.setAnnualFee(data.getAnnualFeeInCents()/100);
                creditCardProduct.setBackgroundColorHex(data.getCardBackgroundHex());
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
            case ProductAnnualFeeChangedEventData.EVENT_NAME -> {
                log.info("Handling projection for ProductDeactivatedEvent");
                final ProductAnnualFeeChangedEventData data = objectMapper.readValue(event.getData(), ProductAnnualFeeChangedEventData.class);
                creditCardProduct = getProductOrThrow(event);
                creditCardProduct.setAnnualFee(data.getAnnualFeeInCents()/100);
            }
            case ProductBackgroundChangedEventData.EVENT_NAME -> {
                log.info("Handling projection for ProductBackgroundChangedEvent");
                final ProductBackgroundChangedEventData data = objectMapper.readValue(event.getData(), ProductBackgroundChangedEventData.class);
                creditCardProduct = getProductOrThrow(event);
                creditCardProduct.setBackgroundColorHex(data.getCardBackgroundHex());
            }
            // For now Ambar is sending all events. But we could update the filter to only give us events related to
            // the properties of products which we actually display.
            // Throwing this will tell ambar to keep going despite something unexpected.
            default -> throw new UnexpectedEventException(event.getEventName());
        }

        creditCardProductProjectionRepository.save(creditCardProduct);
    }

    private CreditCardProduct getProductOrThrow(Payload event) {
        Optional<CreditCardProduct> product = creditCardProductProjectionRepository.findById(event.getAggregateId());
        if (product.isEmpty()) {
            final String msg = "Unable to find Product in projection repository for aggregate: " + event.getAggregateId();
            log.error(msg);
            throw new RuntimeException(msg);
        }
        return product.get();
    }

}

package cloud.ambar.product.enrollment.commands;

import cloud.ambar.common.event.Event;
import cloud.ambar.common.event.store.EventRepository;
import cloud.ambar.product.enrollment.commands.models.RequestEnrollmentCommand;
import cloud.ambar.product.enrollment.events.EnrollmentRequestedEventData;
import cloud.ambar.product.enrollment.exceptions.DuplicateEnrollmentException;
import cloud.ambar.product.enrollment.exceptions.InactiveProductException;
import cloud.ambar.product.enrollment.exceptions.InvalidProductException;
import cloud.ambar.product.enrollment.projection.models.CardProduct;
import cloud.ambar.product.enrollment.query.ProductEnrollmentQueryService;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;
import java.util.Optional;

import static cloud.ambar.common.util.IdGenerator.generateDeterministicId;
import static cloud.ambar.common.util.IdGenerator.generateRandomId;

@Service
@Transactional
@RequiredArgsConstructor
public class ProductEnrollmentCommandService {
    private static final Logger log = LogManager.getLogger(ProductEnrollmentCommandService.class);

    private final EventRepository eventStore;

    private final ObjectMapper objectMapper;

    private final ProductEnrollmentQueryService queryService;

    public void handle(final RequestEnrollmentCommand command) throws JsonProcessingException {
        log.info("Handling " + RequestEnrollmentCommand.class.getSimpleName() +  " command.");

        // 1. Check that the productId is valid.
        final Optional<CardProduct> optionalCardProduct = queryService.getCardProduct(command.getProductId());
        if (optionalCardProduct.isEmpty()) {
            throw new InvalidProductException();
        }
        final CardProduct product = optionalCardProduct.get();
        if (!product.isActive()) {
            throw new InactiveProductException();
        }

        final String compositeId = command.getUserId() + command.getProductId();
        final String eventId = generateDeterministicId(compositeId);
        // First part of validation is to check if this event has already been processed. We expect to create a new
        // unique aggregate from this and subsequent events. If it is already present, then we are processing a duplicate
        // event.
        Optional<Event> priorEntry = eventStore.findByEventId(eventId);
        if (priorEntry.isPresent()) {
            log.info("Found event(s) for eventId - throwing...");
            throw new DuplicateEnrollmentException();
        }


        // Finally, we have passed all the validations, and want to 'accept' (store) the result event. So we will create
        // the resultant event with related details (product definition) and write this to our event store.
        final String aggregateId = generateRandomId();
        final Event event = Event.builder()
                .eventName(EnrollmentRequestedEventData.EVENT_NAME)
                .eventId(eventId)
                .correlationId(eventId)
                .causationID(eventId)
                .aggregateId(aggregateId)
                .version(1)
                .timestamp(LocalDateTime.now())
                .metadata("{}")
                .data(objectMapper.writeValueAsString(
                        EnrollmentRequestedEventData.builder()
                                .userId(command.getUserId())
                                .productId(command.getProductId())
                                .annualIncome(command.getAnnualIncome())
                                .build()
                ))
                .build();

        log.info("Saving Event: " + objectMapper.writeValueAsString(event));
        eventStore.save(event);
        log.info("Successfully handled " + EnrollmentRequestedEventData.EVENT_NAME + " command.");
    }
}

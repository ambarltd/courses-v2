package cloud.ambar.creditCardProduct.commandHandlers;

import cloud.ambar.creditCardProduct.exceptions.InvalidPaymentCycleException;
import cloud.ambar.creditCardProduct.exceptions.InvalidRewardException;
import cloud.ambar.creditCardProduct.events.Event;
import cloud.ambar.creditCardProduct.commands.DefineProductCommand;
import cloud.ambar.creditCardProduct.commands.ProductActivatedCommand;
import cloud.ambar.creditCardProduct.commands.ProductDeactivatedCommand;
import cloud.ambar.creditCardProduct.data.postgre.EventRepository;
import cloud.ambar.creditCardProduct.events.ProductActivatedEvent;
import cloud.ambar.creditCardProduct.events.ProductDeactivatedEvent;
import cloud.ambar.creditCardProduct.events.ProductDefinedEvent;
import cloud.ambar.creditCardProduct.data.models.PaymentCycle;
import cloud.ambar.creditCardProduct.data.models.RewardsType;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;
import java.util.Arrays;
import java.util.Optional;
import java.util.UUID;

@RequiredArgsConstructor
@Service
// TBD needed?
@Transactional
public class ProductCommandService implements CommandService {
    private static final Logger log = LogManager.getLogger(ProductCommandService.class);

    private final EventRepository eventStore;

    private final ObjectMapper objectMapper;

    @Override
    public void handle(DefineProductCommand command) throws JsonProcessingException {
        log.info("Handling " + ProductDefinedEvent.EVENT_NAME + " command.");
        final String eventId = UUID.nameUUIDFromBytes(command.getProductIdentifierForAggregateIdHash().getBytes()).toString();
        // First part of validation is to check if this event has already been processed. We expect to create a new
        // unique aggregate from this and subsequent events. If it is already present, then we are processing a duplicate
        // event.
        Optional<Event> priorEntry = eventStore.findByEventId(eventId);
        if (priorEntry.isPresent()) {
            log.info("Found event(s) for eventId - skipping...");
            return;
        }

        // Next, some simple business validations. These do not rely on any read models (queries)
        if (Arrays.stream(PaymentCycle.values()).noneMatch(p -> p.name().equalsIgnoreCase(command.getPaymentCycle()))) {
            log.error("Invalid payment cycle was specified in command: " + command.getPaymentCycle());
            throw new InvalidPaymentCycleException();
        }

        // ...
        if (Arrays.stream(RewardsType.values()).noneMatch(p -> p.name().equalsIgnoreCase(command.getReward()))) {
            log.error("Invalid reward was specified in command: " + command.getReward());
            throw new InvalidRewardException();
        }

        // Finally, we have passed all the validations, and want to 'accept' (store) the result event. So we will create
        // the resultant event with related details (product definition) and write this to our event store.
        final String aggregateId = UUID.randomUUID().toString();
        final Event event = Event.builder()
                .eventName(ProductDefinedEvent.EVENT_NAME)
                .eventId(eventId)
                .correlationId(eventId)
                .causationID(eventId)
                .aggregateId(aggregateId)
                .version(1)
                .timeStamp(LocalDateTime.now())
                .metadata("")
                .data(objectMapper.writeValueAsString(
                    ProductDefinedEvent.builder()
                        .name(command.getName())
                        .interestInBasisPoints(command.getInterestInBasisPoints())
                        .annualFeeInCents(command.getAnnualFeeInCents())
                        .paymentCycle(command.getPaymentCycle())
                        .creditLimitInCents(command.getCreditLimitInCents())
                        .maxBalanceTransferAllowedInCents(command.getMaxBalanceTransferAllowedInCents())
                        .reward(command.getReward())
                        .cardBackgroundHex(command.getCardBackgroundHex())
                        .build()
                ))
                .build();

        log.info("Saving Event: " + objectMapper.writeValueAsString(event));
        eventStore.save(event);
        log.info("Successfully handled " + ProductDefinedEvent.EVENT_NAME + " command.");
    }

    @Override
    public void handle(ProductActivatedCommand command) {
        log.info("Handling " + ProductActivatedEvent.EVENT_NAME + " command.");
        // Todo:
        //  1. Hydrate the Aggregate
        //  2. Validate the command
        //    -> card currently inactive
        //       This can be done with either a query to the projection DB (async)
        //       Or via the Aggregate (sync) for this trivial example, we will use the aggregate.
        //  3. Update the aggregate (write new event to store)
    }

    @Override
    public void handle(ProductDeactivatedCommand command) {
        log.info("Handling " + ProductDeactivatedEvent.EVENT_NAME + " command.");
        // Todo:
        //  1. Hydrate the Aggregate
        //  2. Validate the command
        //    -> card currently active
        //       This can be done with either a query to the projection DB (async)
        //       Or via the Aggregate (sync) for this trivial example, we will use the aggregate.
        //  3. Update the aggregate (write new event to store)
    }
}

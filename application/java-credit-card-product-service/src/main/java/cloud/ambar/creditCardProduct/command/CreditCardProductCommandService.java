package cloud.ambar.creditCardProduct.command;

import cloud.ambar.creditCardProduct.aggregate.CreditCardProductAggregate;
import cloud.ambar.creditCardProduct.exceptions.InvalidEventException;
import cloud.ambar.creditCardProduct.exceptions.InvalidPaymentCycleException;
import cloud.ambar.creditCardProduct.exceptions.InvalidRewardException;
import cloud.ambar.creditCardProduct.events.Event;
import cloud.ambar.creditCardProduct.command.models.commands.DefineCreditCardProductCommand;
import cloud.ambar.creditCardProduct.command.models.commands.ActivateCreditCardProductCommand;
import cloud.ambar.creditCardProduct.command.models.commands.DeactivateCreditCardProductCommand;
import cloud.ambar.creditCardProduct.database.postgre.EventRepository;
import cloud.ambar.creditCardProduct.events.ProductActivatedEventData;
import cloud.ambar.creditCardProduct.events.ProductDeactivatedEventData;
import cloud.ambar.creditCardProduct.events.ProductDefinedEventData;
import cloud.ambar.creditCardProduct.command.models.validation.PaymentCycle;
import cloud.ambar.creditCardProduct.command.models.validation.RewardsType;
import cloud.ambar.creditCardProduct.exceptions.NoSuchProductException;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;
import java.util.Arrays;
import java.util.List;
import java.util.Optional;
import java.util.UUID;

@Service
@Transactional
@RequiredArgsConstructor
public class CreditCardProductCommandService {
    private static final Logger log = LogManager.getLogger(CreditCardProductCommandService.class);

    private final EventRepository eventStore;

    private final ObjectMapper objectMapper;

    public void handle(DefineCreditCardProductCommand command) throws JsonProcessingException {
        log.info("Handling " + ProductDefinedEventData.EVENT_NAME + " command.");
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
                .eventName(ProductDefinedEventData.EVENT_NAME)
                .eventId(eventId)
                .correlationId(eventId)
                .causationID(eventId)
                .aggregateId(aggregateId)
                .version(1)
                .timestamp(LocalDateTime.now())
                .metadata("")
                .data(objectMapper.writeValueAsString(
                    ProductDefinedEventData.builder()
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
        log.info("Successfully handled " + ProductDefinedEventData.EVENT_NAME + " command.");
    }

    public void handle(ActivateCreditCardProductCommand command) throws JsonProcessingException {
        log.info("Handling " + ProductActivatedEventData.EVENT_NAME + " command.");
        final String aggregateId = command.getId();

        //  1. Hydrate the Aggregate
        final CreditCardProductAggregate aggregate = hydrateAggregateForId(aggregateId);;

        //  2. Validate the command
        //    -> card currently inactive
        //       This can be done with either a query to the projection DB (async)
        //       Or via the Aggregate (sync) for this trivial example, we will use the aggregate.
        if (aggregate.isActive()) {
            throw new InvalidEventException();
        }
        log.info("Product is currently inactive, updating to active!");

        //  3. Update the aggregate (write new event to store)
        final String eventId = UUID.randomUUID().toString();
        final Event event = Event.builder()
                .eventName(ProductActivatedEventData.EVENT_NAME)
                .eventId(eventId)
                .correlationId(aggregateId)
                .causationID(eventId)
                .aggregateId(aggregateId)
                .version(aggregate.getAggregateVersion())
                .timestamp(LocalDateTime.now())
                .metadata("")
                // The top level event EventName, aggregateId, and timestamp really capture everything there is to know
                // about this event.
                .data("")
                .build();

        log.info("Saving Event: " + objectMapper.writeValueAsString(event));
        eventStore.save(event);
        log.info("Successfully handled " + ProductDefinedEventData.EVENT_NAME + " command.");
    }

    public void handle(DeactivateCreditCardProductCommand command) throws JsonProcessingException {
        log.info("Handling " + ProductDeactivatedEventData.EVENT_NAME + " command.");
        final String aggregateId = command.getId();

        //  1. Hydrate the Aggregate
        final CreditCardProductAggregate aggregate = hydrateAggregateForId(aggregateId);

        //  2. Validate the command
        //    -> card currently inactive
        //       This can be done with either a query to the projection DB (async)
        //       Or via the Aggregate (sync) for this trivial example, we will use the aggregate.
        if (!aggregate.isActive()) {
            throw new InvalidEventException();
        }
        log.info("Product is currently active, updating to active!");

        //  3. Update the aggregate (write new event to store)
        final String eventId = UUID.randomUUID().toString();
        final Event event = Event.builder()
                .eventName(ProductDeactivatedEventData.EVENT_NAME)
                .eventId(eventId)
                .correlationId(aggregateId)
                .causationID(eventId)
                .aggregateId(aggregateId)
                .version(aggregate.getAggregateVersion())
                .timestamp(LocalDateTime.now())
                .metadata("")
                // The top level event EventName, aggregateId, and timestamp really capture everything there is to know
                // about this event.
                .data("")
                .build();

        log.info("Saving Event: " + objectMapper.writeValueAsString(event));
        eventStore.save(event);
        log.info("Successfully handled " + ProductDeactivatedEventData.EVENT_NAME + " command.");
    }

    private CreditCardProductAggregate hydrateAggregateForId(String id) {
        final List<Event> productEvents = eventStore.findAllByAggregateId(id);
        final CreditCardProductAggregate aggregate = new CreditCardProductAggregate(id);
        if (productEvents.isEmpty()) {
            throw new NoSuchProductException();
        }

        for (Event event: productEvents) {
            aggregate.apply(event);
        }
        log.info("Hydrated Aggregate: " + aggregate);
        return aggregate;
    }
}

package cloud.ambar.product.enrollment.reaction.service;

import cloud.ambar.common.ambar.event.Payload;
import cloud.ambar.common.event.Event;
import cloud.ambar.common.event.store.EventRepository;
import cloud.ambar.common.exceptions.UnexpectedEventException;
import cloud.ambar.common.reaction.Reactor;
import cloud.ambar.product.enrollment.aggregate.EnrollmentAggregate;
import cloud.ambar.product.enrollment.events.EnrollmentDeclinedEventData;
import cloud.ambar.product.enrollment.projection.models.EnrollmentRequest;
import cloud.ambar.product.enrollment.projection.store.EnrollmentProjectionRepository;
import cloud.ambar.product.management.events.ProductDeactivatedEventData;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;
import java.util.List;
import java.util.UUID;

@Service
@RequiredArgsConstructor
public class CardProductReactionService extends Reactor {
    private static final Logger log = LogManager.getLogger(CardProductReactionService.class);

    private final EventRepository eventStore;
    private final EnrollmentProjectionRepository enrollmentProjectionRepository;
    private final ObjectMapper objectMapper;

    @Transactional
    public void react(final Payload event) {
        // If there are requests to enroll in a card that is deactivated, then we should decline those requests accordingly
        if (event.getEventName().equals(ProductDeactivatedEventData.EVENT_NAME)) {
            log.info("Handling reaction for ProductDeactivatedEvent.");
            log.info("Checking for any enrollments pending for this card and declining them.");
            List<EnrollmentRequest> enrollmentRequestsForCard = enrollmentProjectionRepository.findAllByProductId(event.getAggregateId());

            enrollmentRequestsForCard.forEach(request -> declineRequest(event, request));
        } else {
            // For now Ambar is sending all events. But we could update the filter to only give us events related to
            // the properties of products which we actually display.
            // Throwing this will tell ambar to keep going despite something unexpected.
            throw new UnexpectedEventException(event.getEventName());
        }
    }

    private void declineRequest(Payload eventData, EnrollmentRequest request) {
        try {
            final EnrollmentAggregate aggregate = hydrateAggregateForId(eventStore, request.getId());
            final String eventId = UUID.randomUUID().toString();
            final Event event = Event.builder()
                    .eventName(EnrollmentDeclinedEventData.EVENT_NAME)
                    .eventId(eventId)
                    .correlationId(request.getId())
                    .causationID(eventData.getEventId())
                    .aggregateId(request.getId())
                    .version(aggregate.getAggregateVersion() + 1)
                    .timestamp(LocalDateTime.now())
                    .metadata("Triggered by ProductDeactivatedEvent")
                    .data(objectMapper.writeValueAsString(
                            EnrollmentDeclinedEventData.builder()
                                    .id(request.getId())
                                    .reasonCode("CARD_NOT_AVAILABLE")
                                    .reasonDescription("Card is no longer available.")
                                    .build()
                    ))
                    .build();

            log.info("Saving Event: " + objectMapper.writeValueAsString(event));
            eventStore.save(event);
        } catch (Exception e) {
            log.error("Object mapper faced error");
            log.error(e);
            throw new RuntimeException();
        }
    }
}

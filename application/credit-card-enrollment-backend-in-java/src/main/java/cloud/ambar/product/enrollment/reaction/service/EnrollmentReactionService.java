package cloud.ambar.product.enrollment.reaction.service;

import cloud.ambar.common.ambar.event.Payload;
import cloud.ambar.common.event.Event;
import cloud.ambar.common.event.store.EventRepository;
import cloud.ambar.common.exceptions.UnexpectedEventException;
import cloud.ambar.common.reaction.Reactor;
import cloud.ambar.product.enrollment.events.EnrollmentDeclinedEventData;
import cloud.ambar.product.enrollment.events.EnrollmentPendingReviewEventData;
import cloud.ambar.product.enrollment.events.EnrollmentRequestedEventData;
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
import static cloud.ambar.product.enrollment.util.Constants.MINIMUM_ANNUAL_INCOME_FOR_ENROLLMENT;

@Service
@RequiredArgsConstructor
public class EnrollmentReactionService extends Reactor {
    private static final Logger log = LogManager.getLogger(EnrollmentReactionService.class);

    private final EventRepository eventStore;
    private final ObjectMapper objectMapper;

    @Transactional
    public void react(final Payload event) throws JsonProcessingException {
        // If there are requests to enroll in a card that is deactivated, then we should decline those requests accordingly
        if (event.getEventName().equals(EnrollmentRequestedEventData.EVENT_NAME)) {
            log.info("Handling reaction for EnrollmentRequestedEvent.");
            final EnrollmentRequestedEventData eventData = objectMapper.readValue(event.getData(), EnrollmentRequestedEventData.class);

            final String newEventId = generateDeterministicId(event.getEventId() + "REACTION");
            final Optional<Event> priorEntry = eventStore.findByEventId(newEventId);
            if (priorEntry.isPresent()) {
                log.info("Found event for eventId - skipping...");
                return;
            }

            if (eventData.getAnnualIncome() < MINIMUM_ANNUAL_INCOME_FOR_ENROLLMENT) {
                declineRequest(event, newEventId);
            } else {
                moveRequestToPending(event, newEventId);
            }

        } else {
            // For now Ambar is sending all events. But we could update the filter to only give us events related to
            // the properties of products which we actually display.
            // Throwing this will tell ambar to keep going despite something unexpected.
            throw new UnexpectedEventException(event.getEventName());
        }
    }

    private void declineRequest(Payload eventData, String eventId) throws JsonProcessingException {
        final Event event = Event.builder()
                .eventName(EnrollmentDeclinedEventData.EVENT_NAME)
                .eventId(eventId)
                .correlationId(eventData.getAggregateId())
                .causationID(eventData.getEventId())
                .aggregateId(eventData.getAggregateId())
                .version(2) // The EnrollmentRequest will be the first event in the agg, this reaction will create event #2
                .timestamp(LocalDateTime.now())
                .metadata("{}")
                .data(objectMapper.writeValueAsString(
                        EnrollmentDeclinedEventData.builder()
                                .id(eventData.getAggregateId())
                                .reasonCode("INSUFFICIENT_INCOME")
                                .reasonDescription("Insufficient annual income.")
                                .build()
                ))
                .build();

        log.info("Saving Event: " + objectMapper.writeValueAsString(event));
        eventStore.save(event);
    }

    private void moveRequestToPending(Payload eventData, String eventId) throws JsonProcessingException {
        final Event event = Event.builder()
                .eventName(EnrollmentDeclinedEventData.EVENT_NAME)
                .eventId(eventId)
                .correlationId(eventData.getAggregateId())
                .causationID(eventData.getEventId())
                .aggregateId(eventData.getAggregateId())
                .version(2) // The EnrollmentRequest will be the first event in the agg, this reaction will create event #2
                .timestamp(LocalDateTime.now())
                .metadata("{}")
                .data(objectMapper.writeValueAsString(
                        EnrollmentPendingReviewEventData.builder()
                                .id(eventData.getAggregateId())
                                .build()
                ))
                .build();

        log.info("Saving Event: " + objectMapper.writeValueAsString(event));
        eventStore.save(event);
    }
}

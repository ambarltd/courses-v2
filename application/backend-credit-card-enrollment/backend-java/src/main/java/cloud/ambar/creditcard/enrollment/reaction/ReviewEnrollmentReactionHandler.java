package cloud.ambar.creditcard.enrollment.reaction;

import cloud.ambar.common.aggregate.Aggregate;
import cloud.ambar.common.event.Event;
import cloud.ambar.common.eventstore.AggregateAndEventIdsInLastEvent;
import cloud.ambar.common.eventstore.EventStore;
import cloud.ambar.common.reaction.ReactionHandler;
import cloud.ambar.creditcard.enrollment.aggregate.Enrollment;
import cloud.ambar.creditcard.enrollment.aggregate.EnrollmentStatus;
import cloud.ambar.creditcard.enrollment.event.EnrollmentAccepted;
import cloud.ambar.creditcard.enrollment.event.EnrollmentDeclined;
import cloud.ambar.creditcard.enrollment.event.EnrollmentSubmittedForReview;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.time.Instant;

import static cloud.ambar.common.util.IdGenerator.generateDeterministicId;

@Service
@RequestScope
public class ReviewEnrollmentReactionHandler extends ReactionHandler {

    public ReviewEnrollmentReactionHandler(EventStore eventStore) {
        super(eventStore);
    }

    public void react(final Event event) {
        if (event instanceof EnrollmentSubmittedForReview) {
            final AggregateAndEventIdsInLastEvent aggregateAndEventIdsInLastEvent = eventStore.findAggregate(event.getAggregateId());
            final Aggregate aggregate = aggregateAndEventIdsInLastEvent.getAggregate();
            final String causationId = aggregateAndEventIdsInLastEvent.getEventIdOfLastEvent();
            final String correlationId = aggregateAndEventIdsInLastEvent.getCorrelationIdOfLastEvent();

            if (!(aggregate instanceof Enrollment enrollment)) {
                throw new RuntimeException("Aggregate not found");
            }

            if (!EnrollmentStatus.SUBMITTED_FOR_REVIEW.toString().equals(enrollment.getStatus())) {
                return;
            }

            final String eventId = generateDeterministicId("ReviewedEnrollment" + event.getEventId());

            if (eventStore.doesEventAlreadyExist(eventId)) {
                return;
            }

            if (enrollment.getAnnualIncomeInCents() < 100000) {
                eventStore.saveEvent(EnrollmentDeclined.builder()
                        .eventId(eventId)
                        .aggregateId(enrollment.getAggregateId())
                        .aggregateVersion(enrollment.getAggregateVersion() + 1)
                        .causationId(causationId)
                        .correlationId(correlationId)
                        .recordedOn(Instant.now())
                        .reasonCode("INSUFFICIENT_INCOME")
                        .reasonDescription("Insufficient annual income.")
                        .build());
            } else {
                eventStore.saveEvent(EnrollmentAccepted.builder()
                        .eventId(eventId)
                        .aggregateId(enrollment.getAggregateId())
                        .aggregateVersion(enrollment.getAggregateVersion() + 1)
                        .causationId(causationId)
                        .correlationId(correlationId)
                        .recordedOn(Instant.now())
                        .build());
            }
        }
    }
}

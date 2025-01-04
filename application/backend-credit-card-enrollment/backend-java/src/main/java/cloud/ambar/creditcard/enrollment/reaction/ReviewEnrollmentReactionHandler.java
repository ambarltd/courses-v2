package cloud.ambar.creditcard.enrollment.reaction;

import cloud.ambar.common.aggregate.Aggregate;
import cloud.ambar.common.event.Event;
import cloud.ambar.common.eventstore.AggregateAndEventIdsInLastEvent;
import cloud.ambar.common.reaction.ReactionHandler;
import cloud.ambar.common.eventstore.PostgresTransactionalEventStore;
import cloud.ambar.creditcard.enrollment.aggregate.Enrollment;
import cloud.ambar.creditcard.enrollment.aggregate.EnrollmentStatus;
import cloud.ambar.creditcard.enrollment.event.EnrollmentAccepted;
import cloud.ambar.creditcard.enrollment.event.EnrollmentDeclined;
import cloud.ambar.creditcard.enrollment.event.EnrollmentRequested;
import cloud.ambar.creditcard.enrollment.projection.enrollmentlist.GetEnrollmentList;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.time.Instant;

import static cloud.ambar.common.util.IdGenerator.generateDeterministicId;

@Service
@RequestScope
public class ReviewEnrollmentReactionHandler extends ReactionHandler {
    private final GetEnrollmentList getEnrollmentList;

    public ReviewEnrollmentReactionHandler(
            PostgresTransactionalEventStore postgresTransactionalEventStore,
            GetEnrollmentList getEnrollmentList
    ) {
        super(postgresTransactionalEventStore);
        this.getEnrollmentList = getEnrollmentList;
    }

    public void react(final Event event) {
        if (event instanceof EnrollmentRequested) {
            final AggregateAndEventIdsInLastEvent aggregateAndEventIdsInLastEvent = postgresTransactionalEventStore.findAggregate(event.getAggregateId());
            final Aggregate aggregate = aggregateAndEventIdsInLastEvent.getAggregate();
            final String causationId = aggregateAndEventIdsInLastEvent.getEventIdOfLastEvent();
            final String correlationId = aggregateAndEventIdsInLastEvent.getCorrelationIdOfLastEvent();

            if (!(aggregate instanceof Enrollment enrollment)) {
                throw new RuntimeException("Aggregate not found");
            }

            if (!EnrollmentStatus.Requested.toString().equals(enrollment.getStatus())) {
                return;
            }

            final String reactionEventId = generateDeterministicId("ReviewedEnrollment" + event.getEventId());
            if (postgresTransactionalEventStore.doesEventAlreadyExist(reactionEventId)) {
                return;
            }

            if (getEnrollmentList.isThereAnyAcceptedEnrollmentForUserAndProduct(enrollment.getUserId(), enrollment.getProductId())) {
                postgresTransactionalEventStore.saveEvent(EnrollmentDeclined.builder()
                        .eventId(reactionEventId)
                        .aggregateId(enrollment.getAggregateId())
                        .aggregateVersion(enrollment.getAggregateVersion() + 1)
                        .causationId(causationId)
                        .correlationId(correlationId)
                        .recordedOn(Instant.now())
                        .reasonCode("ALREADY_ACCEPTED")
                        .reasonDescription("You were already accepted to this product.")
                        .build());
                return;
            }


            if (enrollment.getAnnualIncomeInCents() < 1500000) {
                postgresTransactionalEventStore.saveEvent(EnrollmentDeclined.builder()
                        .eventId(reactionEventId)
                        .aggregateId(enrollment.getAggregateId())
                        .aggregateVersion(enrollment.getAggregateVersion() + 1)
                        .causationId(causationId)
                        .correlationId(correlationId)
                        .recordedOn(Instant.now())
                        .reasonCode("INSUFFICIENT_INCOME")
                        .reasonDescription("Insufficient annual income.")
                        .build());
            } else {
                postgresTransactionalEventStore.saveEvent(EnrollmentAccepted.builder()
                        .eventId(reactionEventId)
                        .aggregateId(enrollment.getAggregateId())
                        .aggregateVersion(enrollment.getAggregateVersion() + 1)
                        .causationId(causationId)
                        .correlationId(correlationId)
                        .recordedOn(Instant.now())
                        .reasonCode("ALL_CHECKS_PASSED")
                        .reasonDescription("All checks passed.")
                        .build());
            }
        }
    }
}

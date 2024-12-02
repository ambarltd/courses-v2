package cloud.ambar.creditcard.enrollment.command;

import cloud.ambar.common.aggregate.Aggregate;
import cloud.ambar.common.eventstore.AggregateAndEventIdsInLastEvent;
import cloud.ambar.common.eventstore.EventStore;
import cloud.ambar.creditcard.enrollment.aggregate.Enrollment;
import cloud.ambar.creditcard.enrollment.aggregate.EnrollmentStatus;
import cloud.ambar.creditcard.enrollment.event.EnrollmentRequested;
import cloud.ambar.creditcard.enrollment.event.EnrollmentSubmittedForReview;
import cloud.ambar.creditcard.enrollment.exception.InactiveProductException;
import cloud.ambar.creditcard.enrollment.projection.isproductactive.IsProductActive;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

import java.time.Instant;

import static cloud.ambar.common.util.IdGenerator.generateRandomId;

@Service
@RequiredArgsConstructor
public class EnrollmentCommandHandler {
    private final EventStore eventStore;
    private final IsProductActive isProductActive;

    public void handle(final RequestEnrollmentCommand command) {
        if (!isProductActive.isProductActive(command.getProductId())) {
            throw new InactiveProductException();
        }

        final String eventId = generateRandomId();
        final String aggregateId = generateRandomId();
        final EnrollmentRequested enrollmentRequested = EnrollmentRequested.builder()
                .eventId(eventId)
                .aggregateId(aggregateId)
                .aggregateVersion(1)
                .correlationId(eventId)
                .causationId(eventId)
                .recordedOn(Instant.now())
                .userId(command.getUserId())
                .productId(command.getProductId())
                .annualIncomeInCents(command.getAnnualIncome())
                .build();

        eventStore.saveEvent(enrollmentRequested);
    }

    public void handle(final SubmitEnrollmentForReviewCommand command)
    {
        final AggregateAndEventIdsInLastEvent aggregateAndEventIdsInLastEvent = eventStore.findAggregate(command.getEnrollmentId());
        final Aggregate aggregate = aggregateAndEventIdsInLastEvent.getAggregate();
        final String eventIdOfLastEvent = aggregateAndEventIdsInLastEvent.getEventIdOfLastEvent();
        final String correlationIdOfLastEvent = aggregateAndEventIdsInLastEvent.getCorrelationIdOfLastEvent();

        if (!(aggregate instanceof Enrollment enrollment)) {
            throw new RuntimeException("Aggregate not found");
        }

        if (!EnrollmentStatus.REQUESTED.toString().equals(enrollment.getStatus())) {
            throw new RuntimeException("Enrollment is not in requested status");
        }

        if (!enrollment.getUserId().equals(command.getUserId())) {
            throw new RuntimeException("User is not allowed to submit this enrollment");
        }

        final EnrollmentSubmittedForReview enrollmentSubmittedForReview = EnrollmentSubmittedForReview.builder()
                .eventId(generateRandomId())
                .aggregateId(enrollment.getAggregateId())
                .aggregateVersion(enrollment.getAggregateVersion() + 1)
                .correlationId(correlationIdOfLastEvent)
                .causationId(eventIdOfLastEvent)
                .recordedOn(Instant.now())
                .build();

        eventStore.saveEvent(enrollmentSubmittedForReview);
    }
}

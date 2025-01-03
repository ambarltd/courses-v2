using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Reaction;
using CreditCardEnrollment.CreditCard.Enrollment.Aggregate;
using CreditCardEnrollment.CreditCard.Enrollment.Event;
using CreditCardEnrollment.CreditCard.Enrollment.Projection.EnrollmentList;
using static CreditCardEnrollment.Common.Util.IdGenerator;

namespace CreditCardEnrollment.CreditCard.Enrollment.Reaction;

public class ReviewEnrollmentReactionHandler : ReactionHandler {
   private readonly GetEnrollmentList _getEnrollmentList;

   public ReviewEnrollmentReactionHandler(
       PostgresTransactionalEventStore eventStore,
       GetEnrollmentList getEnrollmentList) 
       : base(eventStore) {
       _getEnrollmentList = getEnrollmentList;
   }

   public override void React(Common.Event.Event @event) {
       if (@event is not EnrollmentRequested) {
           return;
       }

       var aggregateAndEventIds = _postgresTransactionalEventStore.FindAggregate(@event.AggregateId);
       var aggregate = aggregateAndEventIds.Aggregate;
       var causationId = aggregateAndEventIds.EventIdOfLastEvent;
       var correlationId = aggregateAndEventIds.CorrelationIdOfLastEvent;

       if (aggregate is not Aggregate.Enrollment enrollment) {
           throw new InvalidOperationException("Aggregate not found");
       }

       if (enrollment.Status != EnrollmentStatus.Requested) {
           return;
       }

       var reactionEventId = GenerateDeterministicId($"ReviewedEnrollment{@event.EventId}");
       if (_postgresTransactionalEventStore.DoesEventAlreadyExist(reactionEventId)) {
           return;
       }

       if (_getEnrollmentList.IsThereAnyAcceptedEnrollmentForUserAndProduct(enrollment.UserId, enrollment.ProductId)) {
           _postgresTransactionalEventStore.SaveEvent(new EnrollmentDeclined {
               EventId = reactionEventId,
               AggregateId = enrollment.AggregateId,
               AggregateVersion = enrollment.AggregateVersion + 1,
               CausationId = causationId,
               CorrelationId = correlationId,
               RecordedOn = DateTime.UtcNow,
               ReasonCode = "ALREADY_ACCEPTED",
               ReasonDescription = "You were already accepted to this product."
           });
           return;
       }

       if (enrollment.AnnualIncomeInCents < 1500000) {
           _postgresTransactionalEventStore.SaveEvent(new EnrollmentDeclined {
               EventId = reactionEventId,
               AggregateId = enrollment.AggregateId,
               AggregateVersion = enrollment.AggregateVersion + 1,
               CausationId = causationId,
               CorrelationId = correlationId,
               RecordedOn = DateTime.UtcNow,
               ReasonCode = "INSUFFICIENT_INCOME",
               ReasonDescription = "Insufficient annual income."
           });
           return;
       }

       _postgresTransactionalEventStore.SaveEvent(new EnrollmentAccepted {
           EventId = reactionEventId,
           AggregateId = enrollment.AggregateId,
           AggregateVersion = enrollment.AggregateVersion + 1,
           CausationId = causationId,
           CorrelationId = correlationId,
           RecordedOn = DateTime.UtcNow,
           ReasonCode = "ALL_CHECKS_PASSED",
           ReasonDescription = "All checks passed."
       });
   }
}
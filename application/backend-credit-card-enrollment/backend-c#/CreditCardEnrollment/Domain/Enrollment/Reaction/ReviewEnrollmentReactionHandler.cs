using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Reaction;
using CreditCardEnrollment.Domain.Enrollment.Events;
using CreditCardEnrollment.Domain.Enrollment.Projections.EnrollmentList;
using Microsoft.Extensions.Logging;

namespace CreditCardEnrollment.Domain.Enrollment.Reaction;

public class ReviewEnrollmentReactionHandler : ReactionHandler
{
    private readonly IEnrollmentListRepository _enrollmentRepository;
    private readonly ILogger<ReviewEnrollmentReactionHandler> _logger;

    public ReviewEnrollmentReactionHandler(
        PostgresEventStore eventStore,
        IEnrollmentListRepository enrollmentRepository,
        ILogger<ReviewEnrollmentReactionHandler> logger) : base(eventStore)
    {
        _enrollmentRepository = enrollmentRepository;
        _logger = logger;
    }

    protected override async Task HandleEvent(Event @event)
    {
        if (@event is not EnrollmentRequested enrollmentRequested)
        {
            _logger.LogInformation("Ignoring non-EnrollmentRequested event: {EventType}", @event.GetType().Name);
            return;
        }

        _logger.LogInformation(
            "Processing EnrollmentRequested. AggregateId: {AggregateId}, UserId: {UserId}, ProductId: {ProductId}, AnnualIncome: {AnnualIncome}",
            enrollmentRequested.AggregateId,
            enrollmentRequested.UserId,
            enrollmentRequested.ProductId,
            enrollmentRequested.AnnualIncomeInCents
        );

        // Reconstruct aggregate from events
        var events = await EventStore.GetEventsForAggregateAsync(enrollmentRequested.AggregateId);
        _logger.LogInformation("Found {EventCount} events for aggregate", events.Count());
        
        // Find the creation event
        var creationEvent = events.FirstOrDefault() as EnrollmentRequested;
        if (creationEvent == null)
        {
            _logger.LogError("Creation event not found for aggregate {AggregateId}", enrollmentRequested.AggregateId);
            throw new InvalidOperationException("Creation event not found");
        }

        // Reconstruct enrollment using the factory method
        var enrollment = Enrollment.RequestEnrollment(
            creationEvent.EventId,
            creationEvent.AggregateId,
            creationEvent.CorrelationId,
            creationEvent.UserId,
            creationEvent.ProductId,
            creationEvent.AnnualIncomeInCents
        );

        // Apply subsequent events using public methods
        foreach (var evt in events.Skip(1))
        {
            _logger.LogInformation("Applying subsequent event: {EventType}", evt.GetType().Name);
            switch (evt)
            {
                case EnrollmentAccepted accepted:
                    enrollment.Accept(accepted.EventId, accepted.CorrelationId);
                    break;
                case EnrollmentDeclined declined:
                    enrollment.Decline(declined.EventId, declined.CorrelationId, declined.Reason);
                    break;
            }
        }

        if (enrollment.Status != EnrollmentStatus.Requested)
        {
            _logger.LogInformation(
                "Enrollment not in Requested state. Current state: {Status}",
                enrollment.Status
            );
            return;
        }

        var reactionEventId = GenerateDeterministicId("ReviewedEnrollment" + enrollmentRequested.EventId);
        
        // Check if event already exists
        var existingEvents = await EventStore.GetEventsForAggregateAsync(enrollment.Id);
        if (existingEvents.Any(e => e.EventId == reactionEventId))
        {
            _logger.LogInformation("Event already exists: {EventId}", reactionEventId);
            return;
        }

        // Check for existing accepted enrollments
        var existingEnrollments = await _enrollmentRepository.FindByUserId(enrollment.UserId);
        _logger.LogInformation(
            "Found {Count} existing enrollments for user {UserId}",
            existingEnrollments.Count,
            enrollment.UserId
        );

        if (existingEnrollments.Any(e => 
            e.ProductId == enrollment.ProductId && 
            e.Status == EnrollmentStatus.Accepted.ToString()))
        {
            _logger.LogInformation(
                "User already has accepted enrollment for product {ProductId}",
                enrollment.ProductId
            );

            // Use the Decline method which will create and apply the event
            enrollment.Decline(
                reactionEventId,
                enrollmentRequested.CorrelationId,
                "You were already accepted to this product."
            );
            
            // Save the event
            var lastEvent = events.Last();
            await EventStore.SaveEventAsync(lastEvent);
            _logger.LogInformation("Saved EnrollmentDeclined event (duplicate enrollment)");
            return;
        }

        // Check annual income
        if (enrollment.AnnualIncomeInCents < 1_500_000)
        {
            _logger.LogInformation(
                "Annual income {Income} is below minimum requirement of 1,500,000 cents",
                enrollment.AnnualIncomeInCents
            );

            enrollment.Decline(
                reactionEventId,
                enrollmentRequested.CorrelationId,
                "Insufficient annual income."
            );
            
            var lastEvent = events.Last();
            await EventStore.SaveEventAsync(lastEvent);
            _logger.LogInformation("Saved EnrollmentDeclined event (insufficient income)");
            return;
        }

        _logger.LogInformation("All checks passed, accepting enrollment");

        // All checks passed
        enrollment.Accept(
            reactionEventId,
            enrollmentRequested.CorrelationId
        );
        
        var acceptedEvent = events.Last();
        await EventStore.SaveEventAsync(acceptedEvent);
        _logger.LogInformation("Saved EnrollmentAccepted event");
    }
}

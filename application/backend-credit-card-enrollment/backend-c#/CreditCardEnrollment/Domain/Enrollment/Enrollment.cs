using CreditCardEnrollment.Common.Aggregate;
using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Domain.Enrollment.Events;

namespace CreditCardEnrollment.Domain.Enrollment;

public class Enrollment : Aggregate
{
    public string UserId { get; private set; } = string.Empty;
    public string ProductId { get; private set; } = string.Empty;
    public int AnnualIncomeInCents { get; private set; }
    public EnrollmentStatus Status { get; private set; }

    public static Enrollment RequestEnrollment(
        string eventId,
        string aggregateId,
        string correlationId,
        string userId,
        string productId,
        int annualIncomeInCents)
    {
        var enrollment = new Enrollment();
        
        var @event = new EnrollmentRequested
        {
            EventId = eventId,
            AggregateId = aggregateId,
            AggregateVersion = 1,
            CorrelationId = correlationId,
            CausationId = eventId,
            RecordedOn = DateTime.UtcNow,
            UserId = userId,
            ProductId = productId,
            AnnualIncomeInCents = annualIncomeInCents
        };

        enrollment.Apply(@event);
        return enrollment;
    }

    public void Accept(string eventId, string correlationId)
    {
        if (Status != EnrollmentStatus.Requested)
            throw new InvalidOperationException("Can only accept requested enrollments");

        var @event = new EnrollmentAccepted
        {
            EventId = eventId,
            AggregateId = Id,
            AggregateVersion = Version + 1,
            CorrelationId = correlationId,
            CausationId = eventId,
            RecordedOn = DateTime.UtcNow,
            UserId = UserId,
            ProductId = ProductId
        };

        Apply(@event);
    }

    public void Decline(string eventId, string correlationId, string reason)
    {
        if (Status != EnrollmentStatus.Requested)
            throw new InvalidOperationException("Can only decline requested enrollments");

        var @event = new EnrollmentDeclined
        {
            EventId = eventId,
            AggregateId = Id,
            AggregateVersion = Version + 1,
            CorrelationId = correlationId,
            CausationId = eventId,
            RecordedOn = DateTime.UtcNow,
            UserId = UserId,
            ProductId = ProductId,
            Reason = reason
        };

        Apply(@event);
    }

    protected override void Apply(Event @event)
    {
        switch (@event)
        {
            case EnrollmentRequested requested:
                Id = requested.AggregateId;
                UserId = requested.UserId;
                ProductId = requested.ProductId;
                AnnualIncomeInCents = requested.AnnualIncomeInCents;
                Status = EnrollmentStatus.Requested;
                break;

            case EnrollmentAccepted:
                Status = EnrollmentStatus.Accepted;
                break;

            case EnrollmentDeclined:
                Status = EnrollmentStatus.Declined;
                break;

            default:
                throw new ArgumentException($"Unknown event type: {@event.GetType().Name}");
        }
    }
}

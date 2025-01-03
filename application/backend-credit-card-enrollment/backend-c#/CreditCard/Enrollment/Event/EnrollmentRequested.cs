using CreditCardEnrollment.Common.Event;

namespace CreditCardEnrollment.CreditCard.Enrollment.Event;

public class EnrollmentRequested : CreationEvent<Aggregate.Enrollment>
{
    public required string UserId { get; init; }
    public required string ProductId { get; init; }
    public required int AnnualIncomeInCents { get; init; }

    public override Aggregate.Enrollment CreateAggregate()
    {
        return new Aggregate.Enrollment
        {
            AggregateId = AggregateId,
            AggregateVersion = AggregateVersion,
            UserId = UserId,
            ProductId = ProductId,
            Status = Aggregate.EnrollmentStatus.Requested,
            AnnualIncomeInCents = AnnualIncomeInCents,
            EnrollmentFirstRequestedOn = RecordedOn
        };
    }
}

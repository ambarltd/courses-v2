using CreditCardEnrollment.Common.Event;

namespace CreditCardEnrollment.CreditCard.Enrollment.Event;

public class EnrollmentDeclined : TransformationEvent<Aggregate.Enrollment>
{
    public required string ReasonCode { get; init; }
    public required string ReasonDescription { get; init; }

    public override Aggregate.Enrollment TransformAggregate(Aggregate.Enrollment aggregate)
    {
        return new Aggregate.Enrollment
        {
            AggregateId = AggregateId,
            AggregateVersion = AggregateVersion,
            UserId = aggregate.UserId,
            ProductId = aggregate.ProductId,
            Status = Aggregate.EnrollmentStatus.Declined,
            AnnualIncomeInCents = aggregate.AnnualIncomeInCents,
            EnrollmentFirstRequestedOn = aggregate.EnrollmentFirstRequestedOn
        };
    }
}
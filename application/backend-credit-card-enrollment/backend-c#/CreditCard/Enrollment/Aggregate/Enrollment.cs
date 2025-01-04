namespace CreditCardEnrollment.CreditCard.Enrollment.Aggregate;

public class Enrollment : CreditCardEnrollment.Common.Aggregate.Aggregate
{
    public required string UserId { get; init; }
    public required string ProductId { get; init; }
    public required EnrollmentStatus Status { get; init; } 
    public required int AnnualIncomeInCents { get; init; }
    public required DateTime EnrollmentFirstRequestedOn { get; init; }
}
using CreditCardEnrollment.Common.Events;

namespace CreditCardEnrollment.Domain.Enrollment.Events;

public class EnrollmentRequested : CreationEvent
{
    public string UserId { get; set; } = string.Empty;
    public string ProductId { get; set; } = string.Empty;
    public int AnnualIncomeInCents { get; set; }
}

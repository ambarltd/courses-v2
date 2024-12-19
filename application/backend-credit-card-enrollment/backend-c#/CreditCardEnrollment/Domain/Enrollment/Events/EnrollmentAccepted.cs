using CreditCardEnrollment.Common.Events;

namespace CreditCardEnrollment.Domain.Enrollment.Events;

public class EnrollmentAccepted : TransformationEvent
{
    public string UserId { get; set; } = string.Empty;
    public string ProductId { get; set; } = string.Empty;
    public string ReasonDescription { get; set; } = string.Empty;
}
using System;

namespace CreditCardEnrollment.Domain.Enrollment.Query;

public class EnrollmentListItem
{
    public required string Id { get; set; }
    public required string UserId { get; set; }
    public required string ProductId { get; set; }
    public required string ProductName { get; set; }
    public required string Status { get; set; }
    public required string StatusReason { get; set; }
    public DateTime RequestedDate { get; set; }
    public DateTime? ReviewedOn { get; set; }
}

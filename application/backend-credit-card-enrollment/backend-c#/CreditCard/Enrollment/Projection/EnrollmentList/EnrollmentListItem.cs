namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.EnrollmentList;

public class EnrollmentListItem {
    public required string Id { get; init; }
    public required string UserId { get; init; }
    public required string ProductId { get; init; }
    public required string ProductName { get; init; }
    public required DateTime RequestedDate { get; init; }
    public required string Status { get; init; }
    public string? StatusReason { get; init; }
    public DateTime? ReviewedOn { get; init; }
}
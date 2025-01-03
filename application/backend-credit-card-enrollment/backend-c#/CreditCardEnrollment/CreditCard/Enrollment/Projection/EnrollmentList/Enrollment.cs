using MongoDB.Bson.Serialization.Attributes;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.EnrollmentList;

public class Enrollment {
    [BsonId]
    public required string Id { get; init; }
    public required string UserId { get; init; }
    public required string ProductId { get; init; }
    public required DateTime RequestedDate { get; init; }
    public required string Status { get; set; }
    public string? StatusReason { get; set; }
    public DateTime? ReviewedOn { get; set; }
}
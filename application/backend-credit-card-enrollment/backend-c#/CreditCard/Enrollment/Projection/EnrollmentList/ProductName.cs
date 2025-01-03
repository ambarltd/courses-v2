using MongoDB.Bson.Serialization.Attributes;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.EnrollmentList;

public class ProductName {
    [BsonId]
    public required string Id { get; init; }
    public required string Name { get; init; }
}
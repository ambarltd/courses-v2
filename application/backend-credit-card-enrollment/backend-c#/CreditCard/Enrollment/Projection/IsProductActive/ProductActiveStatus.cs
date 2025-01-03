using MongoDB.Bson.Serialization.Attributes;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.IsProductActive;

public class ProductActiveStatus {
    [BsonId]
    public required string Id { get; init; }
    public required bool Active { get; set; }
}
using MongoDB.Bson.Serialization.Attributes;

namespace CreditCardEnrollment.Domain.Product.Projections.ProductActiveStatus;

public class ProductActiveStatus
{
    [BsonId]
    public required string Id { get; set; }
    public bool IsActive { get; set; }
}

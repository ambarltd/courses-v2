using MongoDB.Bson;
using MongoDB.Bson.Serialization.Attributes;

namespace CreditCardEnrollment.Domain.Enrollment.Projections.EnrollmentList;

public class EnrollmentListItem
{
    [BsonId]
    public required string Id { get; set; }
    
    public required string UserId { get; set; }
    
    public required string ProductId { get; set; }
    
    public required string Status { get; set; }
    
    public required string StatusReason { get; set; }
    
    public DateTime RequestedDate { get; set; }
    
    public DateTime? ReviewedOn { get; set; }
}

public class ProductName
{
    [BsonId]
    public required string Id { get; set; }
    
    public required string Name { get; set; }
}

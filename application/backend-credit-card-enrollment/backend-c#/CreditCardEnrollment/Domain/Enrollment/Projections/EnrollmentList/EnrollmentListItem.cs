using MongoDB.Bson;
using MongoDB.Bson.Serialization.Attributes;

namespace CreditCardEnrollment.Domain.Enrollment.Projections.EnrollmentList;

public class EnrollmentListItem
{
    [BsonId]
    public string Id { get; set; }
    
    public string UserId { get; set; }
    
    public string ProductId { get; set; }
    
    public string Status { get; set; }
    
    public string StatusReason { get; set; }
    
    public DateTime RequestedDate { get; set; }
    
    public DateTime? ReviewedOn { get; set; }
}

public class ProductName
{
    [BsonId]
    public string Id { get; set; }
    
    public string Name { get; set; }
}

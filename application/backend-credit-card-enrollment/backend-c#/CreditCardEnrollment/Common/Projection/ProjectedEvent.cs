using MongoDB.Bson.Serialization.Attributes;

namespace CreditCardEnrollment.Common.Projection;

public class ProjectedEvent
{
    [BsonId]
    public string Id { get; set; } = Guid.NewGuid().ToString();
    
    [BsonElement("eventId")]
    public string EventId { get; set; }
    
    [BsonElement("projectionName")]
    public string ProjectionName { get; set; }

    public ProjectedEvent(string eventId, string projectionName)
    {
        EventId = eventId;
        ProjectionName = projectionName;
    }
}

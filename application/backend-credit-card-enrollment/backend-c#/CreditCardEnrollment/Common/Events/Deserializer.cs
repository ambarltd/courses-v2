using System.Text.Json;

namespace CreditCardEnrollment.Common.Events;

public interface IDeserializer
{
    Event Deserialize(string serializedEvent);
}

public class Deserializer : IDeserializer
{
    public Event Deserialize(string serializedEvent)
    {
        var jsonDocument = JsonDocument.Parse(serializedEvent);
        var eventType = jsonDocument.RootElement.GetProperty("type").GetString();
        
        if (string.IsNullOrEmpty(eventType))
        {
            throw new ArgumentException("Event type not found in serialized event");
        }

        var assembly = typeof(Event).Assembly;
        var type = assembly.GetTypes().FirstOrDefault(t => t.Name == eventType);
        
        if (type == null)
        {
            throw new ArgumentException($"Unknown event type: {eventType}");
        }

        return (Event)JsonSerializer.Deserialize(serializedEvent, type)!;
    }
}

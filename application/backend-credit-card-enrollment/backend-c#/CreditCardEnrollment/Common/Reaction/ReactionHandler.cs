using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.EventStore;
using System.Text.Json;

namespace CreditCardEnrollment.Common.Reaction;

public abstract class ReactionHandler
{
    protected readonly PostgresEventStore EventStore;

    protected ReactionHandler(PostgresEventStore eventStore)
    {
        EventStore = eventStore;
    }

    public virtual async Task React(string serializedEvent)
    {
        var jsonDocument = JsonDocument.Parse(serializedEvent);
        var eventType = jsonDocument.RootElement.GetProperty("type").GetString();
        
        if (string.IsNullOrEmpty(eventType))
        {
            throw new ArgumentException("Event type not found in serialized event");
        }

        var @event = DeserializeEvent(serializedEvent, eventType);
        await HandleEvent(@event);
    }

    protected abstract Task HandleEvent(Event @event);

    private Event DeserializeEvent(string serializedEvent, string eventType)
    {
        var assembly = typeof(Event).Assembly;
        var type = assembly.GetTypes().FirstOrDefault(t => t.Name == eventType);
        
        if (type == null)
        {
            throw new ArgumentException($"Unknown event type: {eventType}");
        }

        return (Event)JsonSerializer.Deserialize(serializedEvent, type)!;
    }

    protected string GenerateDeterministicId(string input)
    {
        using var sha256 = System.Security.Cryptography.SHA256.Create();
        var inputBytes = System.Text.Encoding.UTF8.GetBytes(input);
        var hashBytes = sha256.ComputeHash(inputBytes);
        return Convert.ToBase64String(hashBytes).Replace("/", "_").Replace("+", "-").TrimEnd('=');
    }
}

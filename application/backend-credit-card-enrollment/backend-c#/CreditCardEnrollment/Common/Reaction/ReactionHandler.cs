using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.EventStore;
using System.Text.Json;

namespace CreditCardEnrollment.Common.Reaction;

public abstract class ReactionHandler
{
    protected readonly PostgresEventStore EventStore;
    private readonly ILogger<ReactionHandler> _logger;

    protected ReactionHandler(
        PostgresEventStore eventStore,
        ILogger<ReactionHandler> logger)
    {
        EventStore = eventStore;
        _logger = logger;
    }

    public virtual async Task React(string serializedEvent)
    {
        _logger.LogDebug("Deserializing event: {SerializedEvent}", serializedEvent);

        var jsonDocument = JsonDocument.Parse(serializedEvent);
        var eventType = jsonDocument.RootElement.GetProperty("type").GetString();
        
        if (string.IsNullOrEmpty(eventType))
        {
            _logger.LogWarning("Event type not found in serialized event");
            throw new ArgumentException("Event type not found in serialized event");
        }

        _logger.LogInformation("Processing event of type: {EventType}", eventType);

        var @event = DeserializeEvent(serializedEvent, eventType);
        await HandleEvent(@event);

        _logger.LogInformation("Successfully handled event of type: {EventType}", eventType);
    }

    protected abstract Task HandleEvent(Event @event);

    private Event DeserializeEvent(string serializedEvent, string eventType)
    {
        try
        {
            var assembly = typeof(Event).Assembly;
            var type = assembly.GetTypes().FirstOrDefault(t => t.Name == eventType);
            
            if (type == null)
            {
                _logger.LogWarning("Unknown event type: {EventType}", eventType);
                throw new ArgumentException($"Unknown event type: {eventType}");
            }

            _logger.LogDebug("Deserializing event to type: {EventType}", type.FullName);
            return (Event)JsonSerializer.Deserialize(serializedEvent, type)!;
        }
        catch (JsonException ex)
        {
            _logger.LogError(ex, "Failed to deserialize event. Event: {SerializedEvent}", serializedEvent);
            throw new ArgumentException("Failed to deserialize event", ex);
        }
    }

    protected string GenerateDeterministicId(string input)
    {
        using var sha256 = System.Security.Cryptography.SHA256.Create();
        var inputBytes = System.Text.Encoding.UTF8.GetBytes(input);
        var hashBytes = sha256.ComputeHash(inputBytes);
        return Convert.ToBase64String(hashBytes).Replace("/", "_").Replace("+", "-").TrimEnd('=');
    }
}

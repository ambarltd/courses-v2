using CreditCardEnrollment.Common.Events;

namespace CreditCardEnrollment.Common.Aggregate;

public abstract class Aggregate
{
    public string Id { get; protected set; } = string.Empty;
    public int Version { get; protected set; }

    protected abstract void Apply(Event @event);

    public void LoadFromHistory(IEnumerable<Event> events)
    {
        foreach (var @event in events)
        {
            Apply(@event);
            Version = @event.AggregateVersion;
        }
    }
}

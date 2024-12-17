namespace CreditCardEnrollment.Common.Events;

public abstract class Event
{
    public string EventId { get; set; } = string.Empty;
    public string AggregateId { get; set; } = string.Empty;
    public int AggregateVersion { get; set; }
    public string CorrelationId { get; set; } = string.Empty;
    public string CausationId { get; set; } = string.Empty;
    public DateTime RecordedOn { get; set; }
}

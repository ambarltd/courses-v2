namespace CreditCardEnrollment.Common.Event;

public abstract class Event
{
    public required string EventId { get; init; }
    public required string AggregateId { get; init; }
    public required int AggregateVersion { get; init; }
    public required string CorrelationId { get; init; }
    public required string CausationId { get;  init; }
    public required DateTime RecordedOn { get; init; }
}

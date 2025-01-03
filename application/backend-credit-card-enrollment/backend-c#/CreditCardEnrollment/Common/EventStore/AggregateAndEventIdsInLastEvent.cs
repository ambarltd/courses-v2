namespace CreditCardEnrollment.Common.EventStore;

public class AggregateAndEventIdsInLastEvent
{
    public required Aggregate.Aggregate Aggregate { get; init; }
    public required string EventIdOfLastEvent { get; init; }
    public required string CorrelationIdOfLastEvent { get; init; }
}
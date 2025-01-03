namespace CreditCardEnrollment.Common.EventStore;

public class AggregateAndEventIdsInLastEvent<T>
{
    public required T Aggregate { get; init; }
    public required string EventIdOfLastEvent { get; init; }
    public required string CorrelationIdOfLastEvent { get; init; }
}
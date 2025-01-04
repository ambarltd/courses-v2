namespace CreditCardEnrollment.Common.Aggregate;

public abstract class Aggregate
{
    public required string AggregateId { get; init; }
    public required int AggregateVersion { get; init; }
}

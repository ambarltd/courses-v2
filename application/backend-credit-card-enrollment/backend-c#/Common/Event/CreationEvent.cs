namespace CreditCardEnrollment.Common.Event;

public abstract class CreationEvent<T> : Event where T : CreditCardEnrollment.Common.Aggregate.Aggregate
{
    public abstract T CreateAggregate();
}

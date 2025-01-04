namespace CreditCardEnrollment.Common.Event;

public abstract class TransformationEvent<T> : Event where T : CreditCardEnrollment.Common.Aggregate.Aggregate
{
    public abstract T TransformAggregate(T aggregate);
}
using CreditCardEnrollment.Common.Events;

namespace CreditCardEnrollment.Common.Projection;

public abstract class ProjectionHandler
{
    protected abstract void Project(Event @event);
}

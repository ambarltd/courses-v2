using CreditCardEnrollment.Common.Events;

namespace CreditCardEnrollment.Common.Projection;

public abstract class ProjectionHandler
{
    protected abstract void Project(Event @event);

    public void HandleEvent(Event @event)
    {
        Project(@event);
    }
}

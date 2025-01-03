namespace CreditCardEnrollment.Common.Projection;

public abstract class ProjectionHandler
{
    public abstract void Project(Event.Event @event);
}

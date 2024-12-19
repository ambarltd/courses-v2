using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.Projection;

namespace CreditCardEnrollment.Domain.Enrollment.Projections.IsProductActive;

public class IsProductActiveProjectionHandler(ILogger<IsProductActiveProjectionHandler> logger) : ProjectionHandler
{
    protected override void Project(Event @event)
    {
        logger.LogInformation("Processing IsProductActive projection. Event: {@Event}", @event);
        throw new NotImplementedException("IsProductActive projection is not yet implemented");
    }
}

using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.Projection;

namespace CreditCardEnrollment.Domain.Enrollment.Projections.IsProductActive;

public class IsProductActiveProjectionHandler : ProjectionHandler
{
    private readonly ILogger<IsProductActiveProjectionHandler> _logger;

    public IsProductActiveProjectionHandler(
        ILogger<IsProductActiveProjectionHandler> logger)
    {
        _logger = logger;
    }

    protected override void Project(Event @event)
    {
        _logger.LogInformation("Processing IsProductActive projection. Event: {@Event}", @event);
        throw new NotImplementedException("IsProductActive projection is not yet implemented");
    }
}

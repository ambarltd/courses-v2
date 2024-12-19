using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Services;
using CreditCardEnrollment.Domain.Enrollment.Events;
using MediatR;

namespace CreditCardEnrollment.Domain.Enrollment.Controllers.RequestEnrollment;

public class RequestEnrollmentCommandHandler(
    PostgresEventStore eventStore,
    ISessionService sessionService,
    IProductService productService)
    : IRequestHandler<RequestEnrollmentCommand, string>
{
    public async Task<string> Handle(RequestEnrollmentCommand command, CancellationToken cancellationToken)
    {
        var userId = sessionService.GetAuthenticatedUserId(command.SessionToken);

        if (!await productService.IsProductActiveAsync(command.ProductId))
        {
            throw new InvalidOperationException("Product is not active and cannot accept enrollments");
        }

        var eventId = Guid.NewGuid().ToString();
        var aggregateId = Guid.NewGuid().ToString();

        var enrollmentRequestedEvent = new EnrollmentRequested
        {
            EventId = eventId,
            AggregateId = aggregateId,
            AggregateVersion = 1,
            CorrelationId = eventId,
            CausationId = eventId,
            RecordedOn = DateTime.UtcNow,
            UserId = userId,
            ProductId = command.ProductId,
            AnnualIncomeInCents = command.AnnualIncomeInCents
        };

        await eventStore.SaveEventAsync(enrollmentRequestedEvent);

        return aggregateId;
    }
}

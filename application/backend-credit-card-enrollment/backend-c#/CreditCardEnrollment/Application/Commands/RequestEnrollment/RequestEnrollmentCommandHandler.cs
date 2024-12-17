using CreditCardEnrollment.Application.Services;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Domain.Enrollment;
using CreditCardEnrollment.Domain.Enrollment.Events;
using MediatR;

namespace CreditCardEnrollment.Application.Commands.RequestEnrollment;

public class RequestEnrollmentCommandHandler : IRequestHandler<RequestEnrollmentCommand, string>
{
    private readonly PostgresEventStore _eventStore;
    private readonly ISessionService _sessionService;
    private readonly IProductService _productService;

    public RequestEnrollmentCommandHandler(
        PostgresEventStore eventStore,
        ISessionService sessionService,
        IProductService productService)
    {
        _eventStore = eventStore;
        _sessionService = sessionService;
        _productService = productService;
    }

    public async Task<string> Handle(RequestEnrollmentCommand command, CancellationToken cancellationToken)
    {
        var userId = _sessionService.GetAuthenticatedUserId(command.SessionToken);

        if (!await _productService.IsProductActiveAsync(command.ProductId))
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

        await _eventStore.SaveEventAsync(enrollmentRequestedEvent);

        return aggregateId;
    }
}

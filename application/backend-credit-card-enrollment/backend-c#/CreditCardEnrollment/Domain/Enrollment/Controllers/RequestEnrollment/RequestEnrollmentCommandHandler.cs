using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Services;
using CreditCardEnrollment.Domain.Enrollment.Events;
using MediatR;
using Microsoft.Extensions.Logging;

namespace CreditCardEnrollment.Domain.Enrollment.Controllers.RequestEnrollment;

public class RequestEnrollmentCommandHandler : IRequestHandler<RequestEnrollmentCommand, string>
{
    private readonly PostgresEventStore _eventStore;
    private readonly ISessionService _sessionService;
    private readonly IProductService _productService;
    private readonly ILogger<RequestEnrollmentCommandHandler> _logger;

    public RequestEnrollmentCommandHandler(
        PostgresEventStore eventStore,
        ISessionService sessionService,
        IProductService productService,
        ILogger<RequestEnrollmentCommandHandler> logger)
    {
        _eventStore = eventStore;
        _sessionService = sessionService;
        _productService = productService;
        _logger = logger;
    }

    public async Task<string> Handle(RequestEnrollmentCommand command, CancellationToken cancellationToken)
    {
        _logger.LogInformation(
            "Processing enrollment request - Product: {ProductId}, Annual Income: {AnnualIncome}",
            command.ProductId,
            command.AnnualIncomeInCents);

        var userId = _sessionService.GetAuthenticatedUserId(command.SessionToken);
        _logger.LogDebug("Authenticated user ID: {UserId}", userId);

        var isProductActive = await _productService.IsProductActiveAsync(command.ProductId);
        if (!isProductActive)
        {
            _logger.LogWarning(
                "Enrollment request rejected - Product {ProductId} is not active",
                command.ProductId);
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
        
        _logger.LogInformation(
            "Enrollment request processed successfully - AggregateId: {AggregateId}, Product: {ProductId}, User: {UserId}",
            aggregateId,
            command.ProductId,
            userId);

        return aggregateId;
    }
}

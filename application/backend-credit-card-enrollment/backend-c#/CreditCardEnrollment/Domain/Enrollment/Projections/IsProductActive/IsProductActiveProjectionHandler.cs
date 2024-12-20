using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Domain.Product.Events;
using CreditCardEnrollment.Domain.Product.Projections.ProductActiveStatus;
using Microsoft.Extensions.Logging;

namespace CreditCardEnrollment.Domain.Enrollment.Projections.IsProductActive;

public class IsProductActiveProjectionHandler : ProjectionHandler
{
    private readonly ILogger<IsProductActiveProjectionHandler> _logger;
    private readonly IProductActiveStatusRepository _repository;

    public IsProductActiveProjectionHandler(
        ILogger<IsProductActiveProjectionHandler> logger,
        IProductActiveStatusRepository repository)
    {
        _logger = logger;
        _repository = repository;
    }

    protected override async void Project(Event @event)
    {
        _logger.LogInformation(
            "Processing IsProductActive projection for event type {EventType} with ID {EventId}",
            @event.GetType().Name,
            @event.EventId);

        try
        {
            switch (@event)
            {
                case ProductDefined productDefined:
                    await HandleProductDefined(productDefined);
                    break;
                case ProductActivated productActivated:
                    await HandleProductActivated(productActivated);
                    break;
                case ProductDeactivated productDeactivated:
                    await HandleProductDeactivated(productDeactivated);
                    break;
                default:
                    _logger.LogDebug("Ignoring unhandled event type: {EventType}", @event.GetType().Name);
                    break;
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(
                ex,
                "Error processing IsProductActive projection for event {EventType} with ID {EventId}",
                @event.GetType().Name,
                @event.EventId);
            throw;
        }
    }

    private async Task HandleProductDefined(ProductDefined @event)
    {
        _logger.LogInformation(
            "Handling ProductDefined event. ProductId: {ProductId}, Name: {ProductName}",
            @event.AggregateId,
            @event.Name);

        await _repository.Save(new ProductActiveStatus
        {
            Id = @event.AggregateId,
            IsActive = false // Products start as inactive by default
        });

        _logger.LogDebug("Successfully saved initial active status for ProductId: {ProductId}", @event.AggregateId);
    }

    private async Task HandleProductActivated(ProductActivated @event)
    {
        _logger.LogInformation(
            "Handling ProductActivated event. ProductId: {ProductId}",
            @event.AggregateId);

        var status = await _repository.FindById(@event.AggregateId);
        if (status == null)
        {
            _logger.LogWarning(
                "Product not found for ProductActivated event. Creating new record. ProductId: {ProductId}",
                @event.AggregateId);
            status = new ProductActiveStatus
            {
                Id = @event.AggregateId
            };
        }

        status.IsActive = true;
        await _repository.Save(status);

        _logger.LogDebug("Successfully updated product status to Active. ProductId: {ProductId}", @event.AggregateId);
    }

    private async Task HandleProductDeactivated(ProductDeactivated @event)
    {
        _logger.LogInformation(
            "Handling ProductDeactivated event. ProductId: {ProductId}",
            @event.AggregateId);

        var status = await _repository.FindById(@event.AggregateId);
        if (status == null)
        {
            _logger.LogWarning(
                "Product not found for ProductDeactivated event. Creating new record. ProductId: {ProductId}",
                @event.AggregateId);
            status = new ProductActiveStatus
            {
                Id = @event.AggregateId
            };
        }

        status.IsActive = false;
        await _repository.Save(status);

        _logger.LogDebug("Successfully updated product status to Inactive. ProductId: {ProductId}", @event.AggregateId);
    }
}

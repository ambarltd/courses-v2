using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Domain.Product.Events;
using Microsoft.Extensions.Logging;

namespace CreditCardEnrollment.Domain.Product.Projections.ProductActiveStatus;

public class ProductActiveStatusProjectionHandler : ProjectionHandler
{
    private readonly IProductActiveStatusRepository _productActiveStatusRepository;
    private readonly ILogger<ProductActiveStatusProjectionHandler> _logger;

    public ProductActiveStatusProjectionHandler(
        IProductActiveStatusRepository productActiveStatusRepository,
        ILogger<ProductActiveStatusProjectionHandler> logger)
    {
        _productActiveStatusRepository = productActiveStatusRepository;
        _logger = logger;
    }

    protected override async void Project(Event @event)
    {
        _logger.LogInformation("Processing product event: {EventType} for aggregate {AggregateId}", 
            @event.GetType().Name, @event.AggregateId);

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
        }
    }

    private async Task HandleProductDefined(ProductDefined @event)
    {
        _logger.LogInformation("Creating new product active status for product {ProductId}", @event.AggregateId);
        
        await _productActiveStatusRepository.Save(new ProductActiveStatus
        {
            Id = @event.AggregateId,
            Active = false
        });
        
        _logger.LogInformation("Product active status created for product {ProductId}", @event.AggregateId);
    }

    private async Task HandleProductActivated(ProductActivated @event)
    {
        _logger.LogInformation("Activating product status for product {ProductId}", @event.AggregateId);
        
        var productStatus = await _productActiveStatusRepository.FindById(@event.AggregateId);
        if (productStatus == null)
        {
            _logger.LogWarning("Product status not found for product {ProductId}", @event.AggregateId);
            return;
        }

        productStatus.Active = true;
        await _productActiveStatusRepository.Save(productStatus);
        
        _logger.LogInformation("Product status activated for product {ProductId}", @event.AggregateId);
    }

    private async Task HandleProductDeactivated(ProductDeactivated @event)
    {
        _logger.LogInformation("Deactivating product status for product {ProductId}", @event.AggregateId);
        
        var productStatus = await _productActiveStatusRepository.FindById(@event.AggregateId);
        if (productStatus == null)
        {
            _logger.LogWarning("Product status not found for product {ProductId}", @event.AggregateId);
            return;
        }

        productStatus.Active = false;
        await _productActiveStatusRepository.Save(productStatus);
        
        _logger.LogInformation("Product status deactivated for product {ProductId}", @event.AggregateId);
    }
}

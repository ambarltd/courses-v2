using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Domain.Product.Events;
using Microsoft.Extensions.Logging;

namespace CreditCardEnrollment.Domain.Product.Projections.ProductActiveStatus;

public class ProductActiveStatusProjectionHandler(
    IProductActiveStatusRepository productActiveStatusRepository,
    ILogger<ProductActiveStatusProjectionHandler> logger)
    : ProjectionHandler
{
    protected override async void Project(Event @event)
    {
        logger.LogInformation("Processing product event: {EventType} for aggregate {AggregateId}", 
            @event.GetType().Name, @event.AggregateId);

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
                    logger.LogDebug("Event type {EventType} not handled by ProductActiveStatusProjectionHandler", 
                        @event.GetType().Name);
                    break;
            }
        }
        catch (Exception ex)
        {
            logger.LogError(ex, "Error processing product event {EventType} for aggregate {AggregateId}", 
                @event.GetType().Name, @event.AggregateId);
            throw;
        }
    }

    private async Task HandleProductDefined(ProductDefined @event)
    {
        logger.LogInformation("Creating new product active status for product {ProductId}", @event.AggregateId);
        
        await productActiveStatusRepository.Save(new ProductActiveStatus
        {
            Id = @event.AggregateId,
            IsActive = false
        });
        
        logger.LogInformation("Product active status created for product {ProductId}", @event.AggregateId);
    }

    private async Task HandleProductActivated(ProductActivated @event)
    {
        logger.LogInformation("Activating product status for product {ProductId}", @event.AggregateId);
        
        var productStatus = await productActiveStatusRepository.FindById(@event.AggregateId);
        if (productStatus == null)
        {
            logger.LogWarning("Product status not found for product {ProductId}", @event.AggregateId);
            return;
        }

        productStatus.IsActive = true;
        await productActiveStatusRepository.Save(productStatus);
        
        logger.LogInformation("Product status activated for product {ProductId}", @event.AggregateId);
    }

    private async Task HandleProductDeactivated(ProductDeactivated @event)
    {
        logger.LogInformation("Deactivating product status for product {ProductId}", @event.AggregateId);
        
        var productStatus = await productActiveStatusRepository.FindById(@event.AggregateId);
        if (productStatus == null)
        {
            logger.LogWarning("Product status not found for product {ProductId}", @event.AggregateId);
            return;
        }

        productStatus.IsActive = false;
        await productActiveStatusRepository.Save(productStatus);
        
        logger.LogInformation("Product status deactivated for product {ProductId}", @event.AggregateId);
    }
}

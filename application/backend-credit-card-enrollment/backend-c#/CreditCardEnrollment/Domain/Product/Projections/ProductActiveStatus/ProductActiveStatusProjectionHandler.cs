using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Domain.Product.Events;

namespace CreditCardEnrollment.Domain.Product.Projections.ProductActiveStatus;

public class ProductActiveStatusProjectionHandler : ProjectionHandler
{
    private readonly IProductActiveStatusRepository _productActiveStatusRepository;

    public ProductActiveStatusProjectionHandler(IProductActiveStatusRepository productActiveStatusRepository)
    {
        _productActiveStatusRepository = productActiveStatusRepository;
    }

    protected override async void Project(Event @event)
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
        }
    }

    private async Task HandleProductDefined(ProductDefined @event)
    {
        await _productActiveStatusRepository.Save(new ProductActiveStatus
        {
            Id = @event.AggregateId,
            Active = false
        });
    }

    private async Task HandleProductActivated(ProductActivated @event)
    {
        var productStatus = await _productActiveStatusRepository.FindById(@event.AggregateId);
        if (productStatus == null) return;

        productStatus.Active = true;
        await _productActiveStatusRepository.Save(productStatus);
    }

    private async Task HandleProductDeactivated(ProductDeactivated @event)
    {
        var productStatus = await _productActiveStatusRepository.FindById(@event.AggregateId);
        if (productStatus == null) return;

        productStatus.Active = false;
        await _productActiveStatusRepository.Save(productStatus);
    }
}

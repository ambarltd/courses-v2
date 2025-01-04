using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.CreditCard.Product.Event;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.IsProductActive;

public class IsProductActiveProjectionHandler : ProjectionHandler {
    private readonly ProductActiveStatusRepository _productActiveStatusRepository;

    public IsProductActiveProjectionHandler(ProductActiveStatusRepository productActiveStatusRepository) {
        _productActiveStatusRepository = productActiveStatusRepository;
    }

    public override void Project(Common.Event.Event @event) {
        switch (@event) {
            case ProductDefined:
                _productActiveStatusRepository.Save(new ProductActiveStatus {
                    Id = @event.AggregateId,
                    Active = false
                });
                break;

            case ProductActivated:
                var activatedStatus = _productActiveStatusRepository.FindOneById(@event.AggregateId) ?? throw new InvalidOperationException();
                activatedStatus.Active = true;
                _productActiveStatusRepository.Save(activatedStatus);
                break;

            case ProductDeactivated:
                var deactivatedStatus = _productActiveStatusRepository.FindOneById(@event.AggregateId) ?? throw new InvalidOperationException();
                deactivatedStatus.Active = false;
                _productActiveStatusRepository.Save(deactivatedStatus);
                break;
        }
    }
}
namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.IsProductActive;

public class IsProductActive {
    private readonly ProductActiveStatusRepository _productActiveStatusRepository;

    public IsProductActive(ProductActiveStatusRepository productActiveStatusRepository) {
        _productActiveStatusRepository = productActiveStatusRepository;
    }

    public bool IsProductActiveById(string productId) {
        return _productActiveStatusRepository.IsThereAnActiveProductWithId(productId);
    }
}
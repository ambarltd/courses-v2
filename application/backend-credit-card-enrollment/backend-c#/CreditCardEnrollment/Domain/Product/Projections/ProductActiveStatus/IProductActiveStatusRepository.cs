namespace CreditCardEnrollment.Domain.Product.Projections.ProductActiveStatus;

public interface IProductActiveStatusRepository
{
    Task<ProductActiveStatus?> FindById(string id);
    Task Save(ProductActiveStatus status);
}

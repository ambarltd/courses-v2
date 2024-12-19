using System.Threading.Tasks;

namespace CreditCardEnrollment.Domain.Product.Projections.ProductActiveStatus;

public interface IProductActiveStatusRepository
{
    Task<ProductActiveStatus> FindById(string id);
    Task Save(ProductActiveStatus productActiveStatus);
    Task<bool> IsThereAnActiveProductWithId(string productId);
}

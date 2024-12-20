using CreditCardEnrollment.Domain.Product.Projections.ProductActiveStatus;
using MongoDB.Driver;

namespace CreditCardEnrollment.Common.Services;

public interface IProductService
{
    Task<bool> IsProductActiveAsync(string productId);
}

public class ProductService : IProductService
{
    private readonly IMongoCollection<ProductActiveStatus> _productStatusCollection;

    public ProductService(IMongoDatabase database)
    {
        _productStatusCollection = database.GetCollection<ProductActiveStatus>("CreditCard_Enrollment_ProductActiveStatus");
    }

    public async Task<bool> IsProductActiveAsync(string productId)
    {
        var status = await _productStatusCollection
            .Find(p => p.Id == productId)
            .FirstOrDefaultAsync();

        return status?.IsActive ?? false;
    }
}
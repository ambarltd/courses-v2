using MongoDB.Driver;

namespace CreditCardEnrollment.Common.Services;

public interface IProductService
{
    Task<bool> IsProductActiveAsync(string productId);
}

public class ProductService : IProductService
{
    private readonly IMongoCollection<ProductStatus> _productStatusCollection;

    public ProductService(IMongoDatabase database)
    {
        _productStatusCollection = database.GetCollection<ProductStatus>("product_status");
    }

    public async Task<bool> IsProductActiveAsync(string productId)
    {
        var status = await _productStatusCollection
            .Find(p => p.ProductId == productId)
            .FirstOrDefaultAsync();

        return status?.IsActive ?? false;
    }
}

public class ProductStatus
{
    public string ProductId { get; set; } = string.Empty;
    public bool IsActive { get; set; }
}

using MongoDB.Driver;

namespace CreditCardEnrollment.Domain.Enrollment.Projections.EnrollmentList;

public interface IEnrollmentListRepository
{
    Task<EnrollmentListItem> FindById(string id);
    Task<List<EnrollmentListItem>> FindByUserId(string userId);
    Task Save(EnrollmentListItem enrollment);
}

public class EnrollmentListRepository : IEnrollmentListRepository
{
    private readonly IMongoCollection<EnrollmentListItem> _collection;

    public EnrollmentListRepository(IMongoDatabase database)
    {
        _collection = database.GetCollection<EnrollmentListItem>("enrollments");
    }

    public async Task<EnrollmentListItem> FindById(string id)
    {
        return await _collection.Find(e => e.Id == id).FirstOrDefaultAsync();
    }

    public async Task<List<EnrollmentListItem>> FindByUserId(string userId)
    {
        return await _collection.Find(e => e.UserId == userId).ToListAsync();
    }

    public async Task Save(EnrollmentListItem enrollment)
    {
        await _collection.ReplaceOneAsync(
            e => e.Id == enrollment.Id,
            enrollment,
            new ReplaceOptions { IsUpsert = true }
        );
    }
}

public interface IProductNameRepository
{
    Task<ProductName> FindById(string id);
    Task Save(ProductName product);
}

public class ProductNameRepository : IProductNameRepository
{
    private readonly IMongoCollection<ProductName> _collection;

    public ProductNameRepository(IMongoDatabase database)
    {
        _collection = database.GetCollection<ProductName>("product_names");
    }

    public async Task<ProductName> FindById(string id)
    {
        return await _collection.Find(p => p.Id == id).FirstOrDefaultAsync();
    }

    public async Task Save(ProductName product)
    {
        await _collection.ReplaceOneAsync(
            p => p.Id == product.Id,
            product,
            new ReplaceOptions { IsUpsert = true }
        );
    }
}

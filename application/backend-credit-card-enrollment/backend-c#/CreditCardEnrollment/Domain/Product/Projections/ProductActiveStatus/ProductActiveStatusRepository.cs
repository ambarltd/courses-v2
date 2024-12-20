using CreditCardEnrollment.Common.Projection;
using MongoDB.Driver;

namespace CreditCardEnrollment.Domain.Product.Projections.ProductActiveStatus;

public class ProductActiveStatusRepository(
    IMongoTransactionalProjectionOperator mongoOperator,
    IMongoDatabase database)
    : IProductActiveStatusRepository
{
    private const string CollectionName = "CreditCard_Enrollment_ProductActiveStatus";

    public async Task<ProductActiveStatus?> FindById(string id)
    {
        return await mongoOperator.ExecuteInTransaction(async () =>
        {
            var filter = Builders<ProductActiveStatus>.Filter.Eq(x => x.Id, id);
            var collection = database.GetCollection<ProductActiveStatus>(CollectionName);
            return await collection.Find(filter).FirstOrDefaultAsync();
        });
    }

    public async Task Save(ProductActiveStatus productActiveStatus)
    {
        await mongoOperator.ExecuteInTransaction(async () =>
        {
            var collection = database.GetCollection<ProductActiveStatus>(CollectionName);
            var filter = Builders<ProductActiveStatus>.Filter.Eq(x => x.Id, productActiveStatus.Id);
            var options = new ReplaceOptions { IsUpsert = true };
            await collection.ReplaceOneAsync(filter, productActiveStatus, options);
        });
    }

    public async Task<bool> IsThereAnActiveProductWithId(string productId)
    {
        return await mongoOperator.ExecuteInTransaction(async () =>
        {
            var filter = Builders<ProductActiveStatus>.Filter.And(
                Builders<ProductActiveStatus>.Filter.Eq(x => x.Id, productId),
                Builders<ProductActiveStatus>.Filter.Eq(x => x.IsActive, true)
            );
            var collection = database.GetCollection<ProductActiveStatus>(CollectionName);
            var productActiveStatus = await collection.Find(filter).FirstOrDefaultAsync();
            return productActiveStatus != null && productActiveStatus.IsActive;
        });
    }
}

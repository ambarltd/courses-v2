using CreditCardEnrollment.Common.Projection;
using MongoDB.Driver;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.IsProductActive;

public class ProductActiveStatusRepository {
    private readonly MongoTransactionalProjectionOperator _mongoOperator;
    private static string _collectionName = "CreditCard_Enrollment_ProductActiveStatus";

    public ProductActiveStatusRepository(MongoTransactionalProjectionOperator mongoOperator) {
        _mongoOperator = mongoOperator;
    }

    public bool IsThereAnActiveProductWithId(string productId) {
        return _mongoOperator.Find<ProductActiveStatus>(
            _collectionName,
            p => p.Id == productId && p.Active
        ).Any();
    }

    public ProductActiveStatus? FindOneById(string productId) {
        return _mongoOperator.Find<ProductActiveStatus>(
            _collectionName,
            p => p.Id == productId
        ).FirstOrDefault();
    }

    public void Save(ProductActiveStatus productActiveStatus) {
        _mongoOperator.ReplaceOne(
            _collectionName,
            p => p.Id == productActiveStatus.Id, 
            productActiveStatus, 
            new ReplaceOptions { IsUpsert = true }
        );
    }
}
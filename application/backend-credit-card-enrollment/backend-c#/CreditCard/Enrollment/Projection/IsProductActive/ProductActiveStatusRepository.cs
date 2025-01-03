using CreditCardEnrollment.Common.Projection;
using MongoDB.Driver;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.IsProductActive;

public class ProductActiveStatusRepository {
    private readonly MongoTransactionalProjectionOperator _mongoOperator;

    public ProductActiveStatusRepository(MongoTransactionalProjectionOperator mongoOperator) {
        _mongoOperator = mongoOperator;
    }

    public bool IsThereAnActiveProductWithId(string productId) {
        return Collection().Find(p => p.Id == productId && p.Active).Any();
    }

    public ProductActiveStatus? FindOneById(string productId) {
        return Collection().Find(p => p.Id == productId).FirstOrDefault();
    }

    public void Save(ProductActiveStatus productActiveStatus) {
        Collection().ReplaceOne(
            p => p.Id == productActiveStatus.Id, 
            productActiveStatus, 
            new ReplaceOptions { IsUpsert = true }
        );
    }

    private IMongoCollection<ProductActiveStatus> Collection() {
        return _mongoOperator.Operate().GetCollection<ProductActiveStatus>("CreditCard_Enrollment_ProductActiveStatus");
    }
}
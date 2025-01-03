using CreditCardEnrollment.Common.Projection;
using MongoDB.Driver;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.EnrollmentList;

public class ProductNameRepository {
    private readonly MongoTransactionalProjectionOperator _mongoOperator;

    public ProductNameRepository(MongoTransactionalProjectionOperator mongoOperator) {
        _mongoOperator = mongoOperator;
    }

    public void Save(ProductName productName) {
        Collection().ReplaceOne(
            p => p.Id == productName.Id,
            productName,
            new ReplaceOptions { IsUpsert = true }
        );
    }

    public ProductName? FindOneById(string id) {
        return Collection().Find(p => p.Id == id).FirstOrDefault();
    }

    private IMongoCollection<ProductName> Collection() {
        return _mongoOperator.Operate().GetCollection<ProductName>("CreditCard_Enrollment_ProductName");
    }
}
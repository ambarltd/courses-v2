using CreditCardEnrollment.Common.Projection;
using MongoDB.Driver;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.EnrollmentList;

public class ProductNameRepository {
    private readonly MongoTransactionalProjectionOperator _mongoOperator;
    private static string _collectionName = "CreditCard_Enrollment_ProductName";

    public ProductNameRepository(MongoTransactionalProjectionOperator mongoOperator) {
        _mongoOperator = mongoOperator;
    }

    public void Save(ProductName productName) {
        _mongoOperator.ReplaceOne(
            _collectionName,
            p => p.Id == productName.Id, 
            productName, 
            new ReplaceOptions { IsUpsert = true }
        );
    }

    public ProductName? FindOneById(string id) {
        return _mongoOperator.Find<ProductName>(
            _collectionName,
            p => p.Id == id
        ).FirstOrDefault();
    }
}
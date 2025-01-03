using CreditCardEnrollment.Common.Projection;
using MongoDB.Driver;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.EnrollmentList;

public class EnrollmentRepository {
    private readonly MongoTransactionalProjectionOperator _mongoOperator;
    private static string _collectionName = "CreditCard_Enrollment_Enrollment";

    public EnrollmentRepository(MongoTransactionalProjectionOperator mongoOperator) {
        _mongoOperator = mongoOperator;
    }

    public void Save(Enrollment enrollment) {
        _mongoOperator.ReplaceOne(
            _collectionName,
            e => e.Id == enrollment.Id,
            enrollment,
            new ReplaceOptions { IsUpsert = true }
        );
    }

    public Enrollment? FindOneById(string id) {
        return _mongoOperator.Find<Enrollment>(
            _collectionName,
            e => e.Id == id
        ).FirstOrDefault();
    }

    public IEnumerable<Enrollment> FindAllByUserId(string userId) {
        return _mongoOperator.Find<Enrollment>(
            _collectionName,
            e => e.UserId == userId
        ).ToEnumerable();
    }
}
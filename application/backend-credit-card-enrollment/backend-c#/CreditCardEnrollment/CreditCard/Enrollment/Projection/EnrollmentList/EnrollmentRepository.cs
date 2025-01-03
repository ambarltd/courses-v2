using CreditCardEnrollment.Common.Projection;
using MongoDB.Driver;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.EnrollmentList;

public class EnrollmentRepository {
    private readonly MongoTransactionalProjectionOperator _mongoOperator;

    public EnrollmentRepository(MongoTransactionalProjectionOperator mongoOperator) {
        _mongoOperator = mongoOperator;
    }

    public void Save(Enrollment enrollment) {
        Collection().ReplaceOne(
            e => e.Id == enrollment.Id,
            enrollment,
            new ReplaceOptions { IsUpsert = true }
        );
    }

    public Enrollment? FindOneById(string id) {
        return Collection().Find(e => e.Id == id).FirstOrDefault();
    }

    public IEnumerable<Enrollment> FindAllByUserId(string userId) {
        return Collection().Find(e => e.UserId == userId).ToEnumerable();
    }

    private IMongoCollection<Enrollment> Collection() {
        return _mongoOperator.Operate().GetCollection<Enrollment>("CreditCard_Enrollment_Enrollment");
    }
}
using MongoDB.Driver;

namespace CreditCardEnrollment.Common.Projection;

public class MongoTransactionalProjectionOperator {
    private readonly IMongoDatabase _mongoDatabase;
    private readonly IClientSessionHandle _session;

    public MongoTransactionalProjectionOperator(IMongoDatabase mongoDatabase, IClientSessionHandle session) {
        _mongoDatabase = mongoDatabase;
        _session = session;
    }

    public void StartTransaction() {
        if (_session.IsInTransaction) {
            throw new Exception("Transaction to MongoDB already active!");
        }

        var transactionOptions = new TransactionOptions(
            readConcern: ReadConcern.Snapshot,
            writeConcern: WriteConcern.WMajority,
            readPreference: ReadPreference.Primary
        );

        _session.StartTransaction(transactionOptions);
    }

    public IMongoDatabase Operate() {
        if (!_session.IsInTransaction) {
            throw new Exception("Transaction must be active to read or write to MongoDB!");
        }
        return _mongoDatabase;
    }

    public void CommitTransaction() {
        if (!_session.IsInTransaction) {
            throw new Exception("Transaction must be active to commit transaction to MongoDB!");
        }
        _session.CommitTransaction();
    }

    public void AbortDanglingTransactionsAndReturnSessionToPool() {
        if (_session.IsInTransaction) {
            _session.AbortTransaction();
        }
        _session.Dispose();
    }
}
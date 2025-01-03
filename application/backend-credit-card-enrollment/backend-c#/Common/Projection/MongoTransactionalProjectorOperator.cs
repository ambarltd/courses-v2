using CreditCardEnrollment.Common.Util;
using MongoDB.Driver;

namespace CreditCardEnrollment.Common.Projection;

public class MongoTransactionalProjectionOperator {
    private readonly MongoSessionPool _sessionPool;
    private readonly string _databaseName;
    private IClientSessionHandle? _session;
    private IMongoDatabase? _database;

    public MongoTransactionalProjectionOperator(MongoSessionPool sessionPool, string databaseName) {
        _sessionPool = sessionPool;
        _databaseName = databaseName;
        _session = null;
        _database = null;
    }

    public void StartTransaction() {
        if (_session != null) {
            throw new Exception("Session to MongoDB already active!");
        }

        try {
            _session = _sessionPool.StartSession();

            var transactionOptions = new TransactionOptions(
                readConcern: ReadConcern.Snapshot,
                writeConcern: WriteConcern.WMajority,
                readPreference: ReadPreference.Primary
            );

            _session.StartTransaction(transactionOptions);
            _database = _session.Client.GetDatabase(_databaseName);
        } catch (Exception ex) {
            throw new Exception("Failed to start MongoDB transaction", ex);
        }
    }

    public IMongoDatabase Operate() {
        if (_session == null || !_session.IsInTransaction) {
            throw new Exception("Transaction must be active to read or write to MongoDB!");
        }
        return _database ?? throw new Exception("Database is not initialized in the current session.");
    }

    public void CommitTransaction() {
        if (_session == null || !_session.IsInTransaction) {
            throw new Exception("Transaction must be active to commit transaction to MongoDB!");
        }

        try {
            _session.CommitTransaction();
            _session = null;
            _database = null;
        } catch (Exception ex) {
            throw new Exception("Failed to commit MongoDB transaction", ex);
        }
    }

    public void AbortDanglingTransactionsAndReturnSessionToPool() {
        if (_session == null) {
            _database = null;
            return;
        }

        try {
            if (_session.IsInTransaction) {
                _session.AbortTransaction();
            }
        } catch (Exception ex) {
            // todo: log error
        }

        try {
            _session.Dispose();
        } catch (Exception ex) {
            // todo: log error
        }

        _session = null;
        _database = null;
    }
}

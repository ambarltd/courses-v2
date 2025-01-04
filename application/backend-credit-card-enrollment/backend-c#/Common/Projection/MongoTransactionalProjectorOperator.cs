using System.Linq.Expressions;
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
        
        if (_database != null) {
            throw new Exception("Database already initialized in the current session.");
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

    public void CommitTransaction() {
        if (_session == null) {
            throw new Exception("Session must be active to commit transaction to MongoDB!");
        }
        
        if (!_session.IsInTransaction) {
            throw new Exception("Transaction must be active to commit transaction to MongoDB!");
        }

        try {
            _session.CommitTransaction();
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
    
    public IReadOnlyList<TDocument> Find<TDocument>(string collectionName, Expression<Func<TDocument, bool>> filter, FindOptions? options = null)
    {
        var (session, database) = Operate();
        var collection = database.GetCollection<TDocument>(collectionName);
        
        return collection.Find(session, filter, options).ToList();
    } 
    
    public ReplaceOneResult ReplaceOne<TDocument>(string collectionName, Expression<Func<TDocument, bool>> filter, TDocument replacement, ReplaceOptions? options = null)
    {
        var (session, database) = Operate();
        var collection = database.GetCollection<TDocument>(collectionName);
        
        return collection.ReplaceOne(session, filter, replacement, options);
    }
    
    public void InsertOne<TDocument>(string collectionName, TDocument document, InsertOneOptions? options = null)
    {
        var (session, database) = Operate();
        var collection = database.GetCollection<TDocument>(collectionName);
        
        collection.InsertOne(session, document, options);
    }
    
    public long CountDocuments<TDocument>(string collectionName, Expression<Func<TDocument, bool>> filter, CountOptions? options = null)
    {
        var (session, database) = Operate();
        var collection = database.GetCollection<TDocument>(collectionName);
        
        return collection.CountDocuments(session, filter, options);
    }

    private (IClientSessionHandle, IMongoDatabase) Operate() {
        if (_session == null) {
            throw new Exception("Session must be active to read or write to MongoDB!");
        }
        
        if (!_session.IsInTransaction) {
            throw new Exception("Transaction must be active to read or write to MongoDB!");
        }

        if (_database == null) {
            throw new Exception("Database must be initialized in the current session.");
        }

        return (_session, _database);
    }
}

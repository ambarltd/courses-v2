using MongoDB.Driver;

namespace CreditCardEnrollment.Common.Util;

public class MongoSessionPool {
    private readonly IMongoClient _transactionalClient;
    private readonly string _databaseName;

    public MongoSessionPool(string connectionString, string databaseName) {
        var settings = MongoClientSettings.FromConnectionString(connectionString);
        settings.ServerApi = new ServerApi(ServerApiVersion.V1);

        var transactionalSettings = settings.Clone();
        transactionalSettings.MaxConnectionPoolSize = 20;
        transactionalSettings.MinConnectionPoolSize = 5;
        transactionalSettings.MaxConnectionIdleTime = TimeSpan.FromMinutes(10);
        transactionalSettings.MaxConnectionLifeTime = TimeSpan.FromMinutes(30);
        transactionalSettings.WaitQueueTimeout = TimeSpan.FromSeconds(2);
        transactionalSettings.ReplicaSetName = "rs0";

        _transactionalClient = new MongoClient(transactionalSettings);
        _databaseName = databaseName;
    }

    public IClientSessionHandle StartSession() {
        return _transactionalClient.StartSession();
    }
}
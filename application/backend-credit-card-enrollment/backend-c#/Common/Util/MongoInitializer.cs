using MongoDB.Driver;

namespace CreditCardEnrollment.Common.Util;

public class MongoInitializer
{
    private readonly MongoSessionPool _sessionPool;
    private readonly string _databaseName;
    private readonly ILogger<MongoInitializer> _logger;

    public MongoInitializer(
        MongoSessionPool sessionPool,
        string databaseName,
        ILogger<MongoInitializer> logger)
    {
        _sessionPool = sessionPool;
        _databaseName = databaseName;
        _logger = logger;
    }

    public void Initialize()
    {
        using var session = _sessionPool.StartSession();
        var database = session.Client.GetDatabase(_databaseName);

        try
        {
            _logger.LogInformation("Creating collections");
            CreateCollectionIfNotExists(database, "CreditCard_Enrollment_Enrollment");
            CreateCollectionIfNotExists(database, "CreditCard_Enrollment_ProductName");
            CreateCollectionIfNotExists(database, "CreditCard_Enrollment_ProductActiveStatus");
            _logger.LogInformation("Created collections");

            _logger.LogInformation("Creating indexes");
            var enrollmentCollection = database.GetCollection<object>("CreditCard_Enrollment_Enrollment");
            var indexKeysDefinition = Builders<object>.IndexKeys.Ascending("userId");
            enrollmentCollection.Indexes.CreateOne(new CreateIndexModel<object>(indexKeysDefinition));
            _logger.LogInformation("Created indexes");
        }
        catch (Exception e)
        {
            _logger.LogError(e, "Error initializing MongoDB");
            throw;
        }
    }

    private void CreateCollectionIfNotExists(IMongoDatabase database, string collectionName)
    {
        try
        {
            var collections = database.ListCollectionNames().ToList();
            if (!collections.Contains(collectionName))
            {
                database.CreateCollection(collectionName);
                _logger.LogInformation("Created collection {CollectionName}", collectionName);
            }
            else
            {
                _logger.LogInformation("Collection {CollectionName} already exists", collectionName);
            }
        }
        catch (Exception e)
        {
            _logger.LogWarning(e, "Error creating collection {CollectionName}", collectionName);
            throw;
        }
    }
}
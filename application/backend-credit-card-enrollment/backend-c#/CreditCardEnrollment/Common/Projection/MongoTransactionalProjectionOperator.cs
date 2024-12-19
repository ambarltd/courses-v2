using MongoDB.Driver;

namespace CreditCardEnrollment.Common.Projection;

public class MongoTransactionalProjectionOperator(IMongoClient mongoClient, string databaseName)
    : IMongoTransactionalProjectionOperator
{
    private readonly string _databaseName = databaseName;

    public async Task<T> ExecuteInTransaction<T>(Func<Task<T>> operation)
    {
        using var session = await mongoClient.StartSessionAsync();
        session.StartTransaction();

        try
        {
            var result = await operation();
            await session.CommitTransactionAsync();
            return result;
        }
        catch
        {
            await session.AbortTransactionAsync();
            throw;
        }
    }

    public async Task ExecuteInTransaction(Func<Task> operation)
    {
        using var session = await mongoClient.StartSessionAsync();
        session.StartTransaction();

        try
        {
            await operation();
            await session.CommitTransactionAsync();
        }
        catch
        {
            await session.AbortTransactionAsync();
            throw;
        }
    }
}

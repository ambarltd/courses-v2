namespace CreditCardEnrollment.Common.Projection;

public interface IMongoTransactionalProjectionOperator
{
    Task<T> ExecuteInTransaction<T>(Func<Task<T>> operation);
    Task ExecuteInTransaction(Func<Task> operation);
}

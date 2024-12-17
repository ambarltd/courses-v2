using CreditCardEnrollment.Common.Projection;

namespace CreditCardEnrollment.Common.Query;

public interface IQueryHandler<in TQuery, TResult> where TQuery : IQuery<TResult>
{
    Task<TResult> Handle(TQuery query);
}

public abstract class QueryHandler<TQuery, TResult> : IQueryHandler<TQuery, TResult> 
    where TQuery : IQuery<TResult>
{
    protected readonly IMongoTransactionalProjectionOperator MongoOperator;

    protected QueryHandler(IMongoTransactionalProjectionOperator mongoOperator)
    {
        MongoOperator = mongoOperator;
    }

    public abstract Task<TResult> Handle(TQuery query);
}

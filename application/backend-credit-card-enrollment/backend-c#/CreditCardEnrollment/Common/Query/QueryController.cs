using CreditCardEnrollment.Common.Projection;

namespace CreditCardEnrollment.Common.Query;

public class QueryController {
    private readonly MongoTransactionalProjectionOperator _mongoTransactionalProjectionOperator;

    public QueryController(MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator) {
        _mongoTransactionalProjectionOperator = mongoTransactionalProjectionOperator;
    }

    public object ProcessQuery(Query query, QueryHandler queryHandler) {
        try {
            _mongoTransactionalProjectionOperator.StartTransaction();
            var result = queryHandler.HandleQuery(query);
            _mongoTransactionalProjectionOperator.CommitTransaction();
            _mongoTransactionalProjectionOperator.AbortDanglingTransactionsAndReturnSessionToPool();

            return result;
        } catch (Exception ex) {
            _mongoTransactionalProjectionOperator.AbortDanglingTransactionsAndReturnSessionToPool();
            throw new Exception($"Failed to process query: {ex.Message}", ex);
        }
    }
}
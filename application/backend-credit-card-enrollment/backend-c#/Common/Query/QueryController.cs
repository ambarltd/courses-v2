using CreditCardEnrollment.Common.Projection;

namespace CreditCardEnrollment.Common.Query;

public class QueryController {
    private readonly MongoTransactionalProjectionOperator _mongoTransactionalProjectionOperator;
    private readonly ILogger<QueryController> _logger;

    public QueryController(
        MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator,
        ILogger<QueryController> logger
    ) {
        _mongoTransactionalProjectionOperator = mongoTransactionalProjectionOperator;
        _logger = logger;
    }

    protected object ProcessQuery(Query query, QueryHandler queryHandler) {
        try {
            _mongoTransactionalProjectionOperator.StartTransaction();
            var result = queryHandler.HandleQuery(query);
            _mongoTransactionalProjectionOperator.CommitTransaction();
            _mongoTransactionalProjectionOperator.AbortDanglingTransactionsAndReturnSessionToPool();

            return result;
        } catch (Exception ex) {
            _mongoTransactionalProjectionOperator.AbortDanglingTransactionsAndReturnSessionToPool();
            _logger.LogError("Exception in ProcessQuery: {0}, {1}", ex.Message, ex.StackTrace);
            throw new Exception($"Failed to process query: {ex.Message}", ex);
        }
    }
}
package cloud.ambar.common.queryhandler;

import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import lombok.RequiredArgsConstructor;

@RequiredArgsConstructor
abstract public class QueryHandler {
    final protected MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator;
    public abstract Object handleQuery(Query query);
}

package cloud.ambar.common.queryhandler;

import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import java.util.Arrays;
import java.util.stream.Collectors;

@RequiredArgsConstructor
public class QueryController {
    private final MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator;
    private static final Logger log = LogManager.getLogger(QueryController.class);

    public Object processQuery(final Query query, final QueryHandler queryHandler) {
        // We start a Mongo transaction because if a query handler needs to read from a projection,
        // it also needs to do so transactionally to not receive dirty reads.
        try {
            mongoTransactionalProjectionOperator.startTransaction();
            Object object = queryHandler.handleQuery(query);
            mongoTransactionalProjectionOperator.commitTransaction();

            return object;
        } catch (Exception e) {
            log.error("Failed to process reaction command.");
            log.error(e);
            log.error(e.getMessage());

            String stackTraceString = Arrays.stream(e.getStackTrace())
                    .map(StackTraceElement::toString)
                    .collect(Collectors.joining("\n"));
            log.error(stackTraceString);

            try {
                if (mongoTransactionalProjectionOperator.isTransactionActive()) {
                    mongoTransactionalProjectionOperator.abortTransaction();
                }
            } catch (Exception mongoException) {
                log.error("Failed to abort mongo transaction.");
                log.error(mongoException);
                log.error(mongoException.getMessage());
            }

            throw new RuntimeException("Failed to process query with exception: " + e);
        }
    }
}

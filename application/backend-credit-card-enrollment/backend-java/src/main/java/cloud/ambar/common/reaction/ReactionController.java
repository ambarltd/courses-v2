package cloud.ambar.common.reaction;

import cloud.ambar.common.ambar.AmbarHttpRequest;
import cloud.ambar.common.ambar.AmbarResponseFactory;
import cloud.ambar.common.serializedevent.Deserializer;
import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import cloud.ambar.common.eventstore.PostgresTransactionalEventStore;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import java.util.Arrays;
import java.util.stream.Collectors;

@RequiredArgsConstructor
public abstract class ReactionController {
    private final PostgresTransactionalEventStore postgresTransactionalEventStore;
    private final MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator;
    private final Deserializer deserializer;
    private static final Logger log = LogManager.getLogger(ReactionController.class);

    protected String processReactionHttpRequest(final AmbarHttpRequest ambarHttpRequest, final ReactionHandler reactionHandler) {
        try {
            log.info("Reaction controller received http request: " + ambarHttpRequest);

            // We start a PG transaction because reaction handlers need to append to the event store transactionally.
            // I.e., they need to read aggregates and append to them in an ACID fashion.
            // We start a Mongo transaction because if a reaction handler needs to read from a projection,
            // it also needs to do so transactionally to not receive dirty reads.
            postgresTransactionalEventStore.beginTransaction();
            mongoTransactionalProjectionOperator.startTransaction();
            reactionHandler.react(deserializer.deserialize(ambarHttpRequest.getSerializedEvent()));
            postgresTransactionalEventStore.commitTransaction();
            mongoTransactionalProjectionOperator.commitTransaction();

            postgresTransactionalEventStore.abortDanglingTransactionsAndReturnConnectionToPool();
            mongoTransactionalProjectionOperator.abortDanglingTransactionsAndReturnSessionToPool();

            return AmbarResponseFactory.successResponse();
        } catch (Exception e) {
            if (e.getMessage() != null && e.getMessage().startsWith("Unknown event type")) {
                log.warn("Unknown event type. Skipping reaction.");
                log.warn(e);
                postgresTransactionalEventStore.abortDanglingTransactionsAndReturnConnectionToPool();
                mongoTransactionalProjectionOperator.abortDanglingTransactionsAndReturnSessionToPool();
                return AmbarResponseFactory.successResponse();
            }

            log.error("Failed to process reaction http request.");
            log.error(e);
            log.error(e.getMessage());
            String stackTraceString = Arrays.stream(e.getStackTrace())
                    .map(StackTraceElement::toString)
                    .collect(Collectors.joining("\n"));
            log.error(stackTraceString);

            postgresTransactionalEventStore.abortDanglingTransactionsAndReturnConnectionToPool();
            mongoTransactionalProjectionOperator.abortDanglingTransactionsAndReturnSessionToPool();

            return AmbarResponseFactory.retryResponse(e);
        }
    }
}

package cloud.ambar.common.projection;

import cloud.ambar.common.ambar.AmbarHttpRequest;
import cloud.ambar.common.ambar.AmbarResponseFactory;
import cloud.ambar.common.event.Event;
import cloud.ambar.common.serializedevent.Deserializer;
import lombok.*;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.data.mongodb.core.query.Criteria;
import org.springframework.data.mongodb.core.query.Query;

import java.util.Arrays;
import java.util.stream.Collectors;

@RequiredArgsConstructor
public abstract class ProjectionController {
    private final MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator;
    private final Deserializer deserializer;
    private static final Logger log = LogManager.getLogger(ProjectionController.class);

    protected String processProjectionHttpRequest(
            final AmbarHttpRequest ambarHttpRequest,
            final ProjectionHandler projectionHandler,
            final String projectionName) {
        log.info("Reaction controller received http request: " + ambarHttpRequest);

        try {
            Event event = deserializer.deserialize(ambarHttpRequest.getSerializedEvent());

            // We start a Mongo transaction because if a projection handler needs to update a projection,
            // it should do so idempotently by checking if the event has already been projected,
            // and it should do so with a transaction to not receive dirty reads.
            mongoTransactionalProjectionOperator.startTransaction();
            boolean isAlreadyProjected = mongoTransactionalProjectionOperator.operate().count(
                Query.query(
                        Criteria.where("eventId").is(event.getEventId())
                                .and("projectionName").is(projectionName)
                ),
                "ProjectionIdempotency_ProjectedEvent"
            ) != 0;

            if (isAlreadyProjected) {
                mongoTransactionalProjectionOperator.abortDanglingTransactionsAndReturnSessionToPool();
                return AmbarResponseFactory.successResponse();
            }

            mongoTransactionalProjectionOperator.operate().save(
                    ProjectedEvent.builder()
                            .eventId(event.getEventId())
                            .projectionName(projectionName)
                            .build(),
                    "ProjectionIdempotency_ProjectedEvent"
            );

            projectionHandler.project(event);

            mongoTransactionalProjectionOperator.commitTransaction();
            mongoTransactionalProjectionOperator.abortDanglingTransactionsAndReturnSessionToPool();

            return AmbarResponseFactory.successResponse();
        } catch (Exception e) {
            if (e.getMessage() != null && e.getMessage().startsWith("Unknown event type")) {
                log.warn("Unknown event type. Skipping projection.");
                log.warn(e);

                mongoTransactionalProjectionOperator.abortDanglingTransactionsAndReturnSessionToPool();

                return AmbarResponseFactory.successResponse();
            }

            log.error("Failed to process projection http request.");
            log.error(e);
            log.error(e.getMessage());
            String stackTraceString = Arrays.stream(e.getStackTrace())
                    .map(StackTraceElement::toString)
                    .collect(Collectors.joining("\n"));
            log.error(stackTraceString);

            mongoTransactionalProjectionOperator.abortDanglingTransactionsAndReturnSessionToPool();

            return AmbarResponseFactory.retryResponse(e);
        }
    }

    @Builder
    @Setter
    @Getter
    public static class ProjectedEvent {
        @NonNull
        private String eventId;
        @NonNull private String projectionName;
    }
}
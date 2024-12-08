package cloud.ambar.common.projection;

import cloud.ambar.common.ambar.AmbarHttpRequest;
import cloud.ambar.common.ambar.AmbarResponseFactory;
import cloud.ambar.common.event.Event;
import cloud.ambar.common.serializedevent.Deserializer;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.data.mongodb.core.query.Criteria;
import org.springframework.data.mongodb.core.query.Query;

import java.util.Arrays;
import java.util.stream.Collectors;

@RequiredArgsConstructor
public abstract class ProjectionController {
    private final Deserializer deserializer;
    private final MongoTransactionalAPI mongoTransactionalAPI;
    private static final Logger log = LogManager.getLogger(ProjectionController.class);

    protected String processHttpRequest(
            final AmbarHttpRequest ambarHttpRequest,
            final ProjectionHandler projectionHandler,
            final String projectionName) {
        log.info("Projection received http request: {}", ambarHttpRequest);

        try {
            Event event = deserializer.deserialize(ambarHttpRequest.getSerializedEvent());

            mongoTransactionalAPI.startTransaction();
            boolean isAlreadyProjected = mongoTransactionalAPI.operate().count(
                Query.query(
                        Criteria.where("eventId").is(event.getEventId())
                                .and("projectionName").is(projectionName)
                ),
                "ProjectionIdempotency_ProjectedEvent"
            ) != 0;

            if (isAlreadyProjected) {
                return AmbarResponseFactory.successResponse();
            }

            mongoTransactionalAPI.operate().save(
                    ProjectedEvent.builder()
                            .eventId(event.getEventId())
                            .projectionName(projectionName)
                            .build(),
                    "ProjectionIdempotency_ProjectedEvent"
            );

            projectionHandler.project(event);

            mongoTransactionalAPI.commitTransaction();
            return AmbarResponseFactory.successResponse();
        } catch (Exception e) {
            if (e.getMessage() != null && e.getMessage().startsWith("Unknown event type")) {
                log.warn("Unknown event type. Skipping projection.");
                log.warn(e);
                return AmbarResponseFactory.successResponse();
            }

            log.error("Failed to process projection http request.");
            log.error(e);
            log.error(e.getMessage());
            String stackTraceString = Arrays.stream(e.getStackTrace())
                    .map(StackTraceElement::toString)
                    .collect(Collectors.joining("\n"));
            log.error(stackTraceString);
            if (mongoTransactionalAPI.isTransactionActive()) {
                mongoTransactionalAPI.abortTransaction();
                mongoTransactionalAPI.closeSession();
            }
            return AmbarResponseFactory.retryResponse(e);
        }
    }
}
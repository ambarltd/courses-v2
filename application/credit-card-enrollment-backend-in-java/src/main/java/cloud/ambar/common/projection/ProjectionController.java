package cloud.ambar.common.projection;

import cloud.ambar.common.ambar.httprequest.AmbarHttpRequest;
import cloud.ambar.common.ambar.httpresponse.AmbarResponseFactory;
import cloud.ambar.common.serializedevent.Deserializer;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

public abstract class ProjectionController {
    protected Deserializer deserializer;
    private static final Logger log = LogManager.getLogger(ProjectionController.class);
    protected String processHttpRequest(final AmbarHttpRequest ambarHttpRequest, final ProjectionHandler projectionService) {
        try {
            log.info("Projection received http request: " + ambarHttpRequest);

            projectionService.project(deserializer.deserialize(ambarHttpRequest.getSerializedEvent()));
            return AmbarResponseFactory.successResponse();
        } catch (Exception e) {
            log.error("Failed to process projection http request.");
            log.error(e);
            log.error(e.getMessage());
            return AmbarResponseFactory.retryResponse(e.getMessage());
        }
    }
}

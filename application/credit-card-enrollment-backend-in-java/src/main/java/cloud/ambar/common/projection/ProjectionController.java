package cloud.ambar.common.projection;

import cloud.ambar.common.ambar.event.AmbarEvent;
import cloud.ambar.common.ambar.response.Ambar;
import cloud.ambar.common.ambar.response.AmbarResponse;
import cloud.ambar.common.exceptions.UnexpectedEventException;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

public abstract class ProjectionController {
    private static final Logger log = LogManager.getLogger(ProjectionController.class);
    public AmbarResponse processEvent(final AmbarEvent ambarEvent, final Projector projectionService) {
        try {
            log.info("Got event: " + ambarEvent);

            projectionService.project(ambarEvent.getPayload());
            return Ambar.successResponse();
        } catch (UnexpectedEventException e) {
            log.warn("Got unexpected event at projection endpoint from Ambar...");
            log.warn("Check Filter configuration for Ambar, dropping event and continuing...");
            return Ambar.keepGoingResponse(e.getMessage());
        } catch (Exception e) {
            log.error("Failed to process projection event!");
            log.error(e);
            log.error(e.getMessage());
            return Ambar.retryResponse(e.getMessage());
        }
    }
}

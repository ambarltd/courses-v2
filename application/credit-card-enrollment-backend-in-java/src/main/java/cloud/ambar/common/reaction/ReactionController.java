package cloud.ambar.common.reaction;

import cloud.ambar.common.ambar.event.AmbarEvent;
import cloud.ambar.common.ambar.response.Ambar;
import cloud.ambar.common.ambar.response.AmbarResponse;
import cloud.ambar.common.exceptions.UnexpectedEventException;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

public class ReactionController {
    private static final Logger log = LogManager.getLogger(ReactionController.class);


    public AmbarResponse processEvent(final AmbarEvent ambarEvent, final Reactor reactionService) {
        try {
            log.info("Got event: " + ambarEvent);

            reactionService.react(ambarEvent.getPayload());
            return Ambar.successResponse();
        } catch (UnexpectedEventException e) {
            log.warn("Got unexpected event at reaction endpoint from Ambar...");
            log.warn("Check Filter configuration for Ambar, dropping event and continuing...");
            return Ambar.keepGoingResponse(e.getMessage());
        } catch (Exception e) {
            log.error("Failed to process reaction event!");
            log.error(e);
            log.error(e.getMessage());
            return Ambar.retryResponse(e.getMessage());
        }
    }

}

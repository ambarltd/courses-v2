package cloud.ambar.common.reaction;

import cloud.ambar.common.ambar.httprequest.AmbarHttpRequest;
import cloud.ambar.common.ambar.httpresponse.AmbarResponseFactory;
import cloud.ambar.common.serializedevent.Deserializer;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

@RequiredArgsConstructor
public abstract class ReactionController {
    private final Deserializer deserializer;
    private static final Logger log = LogManager.getLogger(ReactionController.class);

    public String processHttpRequest(final AmbarHttpRequest ambarHttpRequest, final ReactionHandler reactionHandler) {
        try {
            log.info("Reaction received http request: " + ambarHttpRequest);

            reactionHandler.react(deserializer.deserialize(ambarHttpRequest.getSerializedEvent()));
            return AmbarResponseFactory.successResponse();
        } catch (Exception e) {
            log.error("Failed to process reaction http request.");
            log.error(e);
            log.error(e.getMessage());
            return AmbarResponseFactory.retryResponse(e);
        }
    }
}

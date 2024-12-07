package cloud.ambar.common.reaction;

import cloud.ambar.common.ambar.AmbarHttpRequest;
import cloud.ambar.common.ambar.AmbarResponseFactory;
import cloud.ambar.common.serializedevent.Deserializer;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import java.util.Arrays;
import java.util.stream.Collectors;

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
            if (e.getMessage() != null && e.getMessage().startsWith("Unknown event type")) {
                log.warn("Unknown event type. Skipping reaction.");
                log.warn(e);
                return AmbarResponseFactory.successResponse();
            }

            log.error("Failed to process reaction http request.");
            log.error(e);
            log.error(e.getMessage());
            String stackTraceString = Arrays.stream(e.getStackTrace())
                    .map(StackTraceElement::toString)
                    .collect(Collectors.joining("\n"));
            log.error(stackTraceString);
            return AmbarResponseFactory.retryResponse(e);
        }
    }
}

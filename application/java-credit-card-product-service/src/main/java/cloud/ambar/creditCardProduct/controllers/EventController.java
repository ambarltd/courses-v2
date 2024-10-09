package cloud.ambar.creditCardProduct.controllers;

import cloud.ambar.common.ambar.AmbarEvent;
import cloud.ambar.common.ambar.AmbarResponse;
import cloud.ambar.common.ambar.Error;
import cloud.ambar.common.ambar.ErrorPolicy;
import cloud.ambar.common.ambar.Result;
import cloud.ambar.common.ambar.Success;
import cloud.ambar.common.models.Event;
import cloud.ambar.creditCardProduct.events.projection.ProductProjectorService;
import com.fasterxml.jackson.databind.ObjectMapper;
import jakarta.servlet.ServletInputStream;
import jakarta.servlet.http.HttpServletRequest;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.http.MediaType;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RestController;

import java.io.ByteArrayOutputStream;
import java.io.IOException;

/**
 * This controller is responsible for updating the read models from events written to the eventstore (postgre).
 * It is notified of new events on an endpoint, and then will perform any necessary actions to retrieve
 * and update corresponding models in the ReadModelRepository (mongo).
 *
 * This is the Projection/Reaction side of our application
 * Note: This service does not write any new events in response to incoming events, and thus does not have a reaction portion
 */
@RestController
public class EventController {
    private static final Logger log = LogManager.getLogger(EventController.class);

    private final ProductProjectorService productProjectorService;

    private final ObjectMapper objectMapper;

    public EventController(final ProductProjectorService productProjectorService) {
        this.productProjectorService = productProjectorService;
        this.objectMapper = new ObjectMapper();
    }

    @PostMapping(value = "/api/v1/credit_card_product/product/projection",
            consumes = MediaType.APPLICATION_OCTET_STREAM_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public AmbarResponse handleEvent(HttpServletRequest httpServletRequest) {
        try {
            final AmbarEvent ambarEvent = extractEvent(httpServletRequest);
            log.info("Got event: " + ambarEvent);

            final Event event = objectMapper.convertValue(ambarEvent.getPayload(), Event.class);

            productProjectorService.project(event);
            return successResponse();
        } catch (Exception e) {
            log.error("Failed to process projection event!");
            log.error(e);
            return retryResponse(e.getMessage());
        }
    }

    private AmbarResponse retryResponse(String err) {
        return AmbarResponse.builder()
                .result(Result.builder()
                        .error(Error.builder()
                                .policy(ErrorPolicy.MUST_RETRY.toString())
                                .description(err)
                                .build())
                        .build())
                .build();
    }

    private AmbarResponse successResponse() {
        return AmbarResponse.builder()
                .result(Result.builder()
                        .success(new Success())
                        .build())
                .build();
    }

    // Pulls the Ambar event as a string from the request.
    private AmbarEvent extractEvent(HttpServletRequest httpServletRequest) throws IOException {
        final ServletInputStream inputStream;

        try {
            inputStream = httpServletRequest.getInputStream();
        } catch (IOException e) {
            throw new RuntimeException(e);
        }

        final ByteArrayOutputStream result = new ByteArrayOutputStream();
        byte[] buffer = new byte[1024];
        for (int length; (length = inputStream.read(buffer)) != -1; ) {
            result.write(buffer, 0, length);
        }

        log.info("Got message: " + result);

        return objectMapper.convertValue(result.toString(), AmbarEvent.class);
    }
}

package cloud.ambar.creditCardProduct.controllers;

import cloud.ambar.common.ambar.AmbarEvent;
import cloud.ambar.common.ambar.ErrorKeepGoing;
import cloud.ambar.creditCardProduct.events.projection.ProductProjectorService;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import jakarta.servlet.ServletInputStream;
import jakarta.servlet.http.HttpServletRequest;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.apache.logging.log4j.core.util.IOUtils;
import org.springframework.http.MediaType;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RestController;

import java.io.BufferedReader;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.nio.charset.StandardCharsets;
import java.util.stream.Collectors;

import static org.springframework.http.MediaType.APPLICATION_JSON;

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
    public String handleEvent(HttpServletRequest httpServletRequest) throws IOException {
        ServletInputStream inputStream;

        try {
            inputStream = httpServletRequest.getInputStream();
        } catch (IOException e) {
            throw new RuntimeException(e);
        }

        ByteArrayOutputStream result = new ByteArrayOutputStream();
        byte[] buffer = new byte[1024];
        for (int length; (length = inputStream.read(buffer)) != -1; ) {
            result.write(buffer, 0, length);
        }

        String str = result.toString();
        log.info("Got event: " + str);

        final ErrorKeepGoing error = new ErrorKeepGoing();
        log.info("Returning canned retry response: " + error);
        return objectMapper.writeValueAsString(error);
    }

}

package cloud.ambar.creditCardProduct.controllers;

import cloud.ambar.common.ambar.AmbarEvent;
import cloud.ambar.common.ambar.ErrorKeepGoing;
import cloud.ambar.creditCardProduct.events.projection.ProductProjectorService;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.http.MediaType;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RestController;

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
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public String handleEvent(@RequestBody AmbarEvent event) throws JsonProcessingException {
        log.info("Got event: " + event);
        // Todo:  Deserialize the AmbarEvent and get the payload into an internal event before having the
        //        projector service handle it.
        final ErrorKeepGoing error = new ErrorKeepGoing();
        log.info("Returning canned retry response: " + error);
        return objectMapper.writeValueAsString(error);
    }

}

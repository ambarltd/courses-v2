package cloud.ambar.creditCardProduct.controllers;

import cloud.ambar.common.ambar.AmbarEvent;
import cloud.ambar.common.ambar.ErrorMustRetry;
import cloud.ambar.creditCardProduct.data.mongo.ReadModelRepository;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RestController;

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

    private final ReadModelRepository readModelRepository;

    private final ObjectMapper objectMapper;

    @Autowired
    public EventController(final ReadModelRepository readModelRepository) {
        this.readModelRepository = readModelRepository;
        this.objectMapper = new ObjectMapper();
    }

    @PostMapping(value = "/api/v1/credit_card_product/product")
    public String handleEvent(AmbarEvent event) throws JsonProcessingException {
        log.info(event);
        return objectMapper.writeValueAsString(new ErrorMustRetry());
    }

}

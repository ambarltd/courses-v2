package cloud.ambar.product.management.controllers;

import cloud.ambar.common.ambar.event.AmbarEvent;
import cloud.ambar.common.ambar.response.AmbarResponse;
import cloud.ambar.common.projection.ProjectionController;
import cloud.ambar.product.management.projection.ProductManagementProjectionService;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.http.HttpStatus;
import org.springframework.http.MediaType;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseStatus;
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
@RequiredArgsConstructor
@RequestMapping("/api/v1/credit_card_product/product")
public class ManagementProjectionController extends ProjectionController {
    private static final Logger log = LogManager.getLogger(ManagementProjectionController.class);

    private final ProductManagementProjectionService productManagementProjectionService;

    private final ObjectMapper objectMapper;

    @PostMapping(value = "/projection",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    @ResponseStatus(HttpStatus.OK)
    public AmbarResponse handleEvent(@RequestBody String event) throws JsonProcessingException {
        log.info("Handling Card Product Projection");
        final AmbarEvent ambarEvent = objectMapper.readValue(event, AmbarEvent.class);
        return processEvent(ambarEvent, productManagementProjectionService);
    }
}

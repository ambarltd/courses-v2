package cloud.ambar.product.management.controllers;

import cloud.ambar.common.ambar.event.AmbarEvent;
import cloud.ambar.common.ambar.response.AmbarResponse;
import cloud.ambar.common.projection.ProjectionController;
import cloud.ambar.product.management.projection.ProductManagementProjectionService;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;

/**
 * This controller is responsible for updating the read models from events written to the eventstore (postgre).
 * It is notified of new events on an endpoint, and then will perform any necessary actions to retrieve
 * and update corresponding models in the ReadModelRepository (mongo).
 *
 * This is the Projection/Reaction side of our application
 * Note: This service does not write any new events in response to incoming events, and thus does not have a reaction portion
 */
@Controller
@RequiredArgsConstructor
public class ManagementProjectionController extends ProjectionController {

    private final ProductManagementProjectionService productManagementProjectionService;

    @PostMapping(value = "/api/v1/credit_card_product/product/projection")
    public AmbarResponse handleEvent(@RequestBody AmbarEvent ambarEvent) {
        return processEvent(ambarEvent, productManagementProjectionService);
    }
}

package cloud.ambar.product.management.controllers;

import cloud.ambar.product.management.projection.models.CreditCardProduct;
import cloud.ambar.product.management.query.QueryService;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

/**
 * This controller will handle endpoints related to querying details about products for the front end.
 * These endpoints do not handle any commands and just return things back from the ReadModelRepository as
 * written by projections and reactions.
 * This is the Read side of our application
 * Requests to handle:
 *  - ListProducts
 */
@RestController
public class QueryController {
    private static final Logger log = LogManager.getLogger(QueryController.class);

    private final QueryService queryService;

    private final ObjectMapper objectMapper;

    public QueryController(QueryService queryService) {
        this.queryService = queryService;
        this.objectMapper = new ObjectMapper();
    }

    @PostMapping(value = "/api/v1/credit_card_product/product/list-items")
    public String listItems() throws JsonProcessingException {
        log.info("Listing all products from ProjectionRepository");
        List<CreditCardProduct> creditCardProducts = queryService.getAllCreditCardProducts();

        return objectMapper.writeValueAsString(creditCardProducts);
    }

}

package cloud.ambar.creditCardProduct.controllers;

import cloud.ambar.creditCardProduct.commandHandlers.ProductService;
import cloud.ambar.creditCardProduct.models.projection.ProductListItem;
import cloud.ambar.creditCardProduct.query.ListProductsQueryHandler;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RestController;

import java.net.http.HttpRequest;
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

    private final ListProductsQueryHandler listProductsQueryHandler;

    @Autowired
    public QueryController(ListProductsQueryHandler listProductsQueryHandler) {
        this.listProductsQueryHandler = listProductsQueryHandler;
    }

    @PostMapping(value = "/api/v1/credit_card_product/product/list-items")
    public String listItems(HttpRequest request) {
        log.debug(request);
        List<ProductListItem> products = listProductsQueryHandler.scanProductListItems();
        // Todo: Create the response shape and serialize it.
        return "{}";
    }

}

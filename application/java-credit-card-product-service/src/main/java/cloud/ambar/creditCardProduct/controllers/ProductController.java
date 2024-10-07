package cloud.ambar.creditCardProduct.controllers;

import cloud.ambar.creditCardProduct.commandHandlers.ProductHandler;
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

@RestController
public class ProductController {
    private static final Logger log = LogManager.getLogger(ProductController.class);

    private final ProductService productService;

    private final ListProductsQueryHandler listProductsQueryHandler;

    @Autowired
    public ProductController(ProductService productService, ListProductsQueryHandler listProductsQueryHandler) {
        this.productService = productService;
        this.listProductsQueryHandler = listProductsQueryHandler;
    }

    private ListProductsQueryHandler listProductsHandler;
    @PostMapping(value = "/api/v1/credit_card_product/product/list-items")
    public String listItems(HttpRequest request) {
        log.debug(request);
        List<ProductListItem> products = listProductsQueryHandler.scanProductListItems();
        // Todo: Create the response shape and serialize it.
        return "{}";
    }

}

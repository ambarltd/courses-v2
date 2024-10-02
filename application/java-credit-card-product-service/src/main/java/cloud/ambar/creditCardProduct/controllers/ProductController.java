package cloud.ambar.creditCardProduct.controllers;

import cloud.ambar.creditCardProduct.queryHandler.ListProductsHandler;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RestController;

import java.net.http.HttpRequest;

@RestController
public class ProductController {

    private ListProductsHandler listProductsHandler;
    @PostMapping(value = "/api/v1/credit_card_product/product/list-items")
    public String getTestData(HttpRequest request) {

        return "temp";
    }

}

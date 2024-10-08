package cloud.ambar.creditCardProduct.controllers;

import cloud.ambar.creditCardProduct.commandHandlers.ProductService;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.RestController;

/**
 * This controller will handle requests from the frontend which are commands that result in events written
 * to the event store. Events such as defining a product, activating a product, and deactivating a product.
 * It will leverage the read side of the application to perform validations and determine if we should accept
 * or reject a command before recording it.
 * This is the write side of our application.
 * Requests (Commands) to handle:
 *  - DefineProduct
 *  - ActivateProduct
 *  - DeactivateProduct
 */
@RestController
public class CommandController {
    private static final Logger log = LogManager.getLogger(CommandController.class);

    private final ProductService productService;

    @Autowired
    public CommandController(final ProductService productService) {
        this.productService = productService;
    }


    /**
     * Todo: Handle posts to:
     * - Define a product
     * - Activate a product
     * - Deactivate a product
     */

}

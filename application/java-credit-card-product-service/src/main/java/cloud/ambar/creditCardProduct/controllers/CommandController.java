package cloud.ambar.creditCardProduct.controllers;

import cloud.ambar.creditCardProduct.commandHandlers.ProductCommandService;
import cloud.ambar.creditCardProduct.commands.DefineProductCommand;
import cloud.ambar.creditCardProduct.commands.ProductActivatedCommand;
import cloud.ambar.creditCardProduct.commands.ProductDeactivatedCommand;
import com.fasterxml.jackson.core.JsonProcessingException;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseStatus;

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
@Controller
@RequestMapping("/api/v1/credit_card_product/product")
public class CommandController {
    private static final Logger log = LogManager.getLogger(CommandController.class);

    private final ProductCommandService productService;

    @Autowired
    public CommandController(final ProductCommandService productService) {
        this.productService = productService;
    }

    @PostMapping
    @ResponseStatus(HttpStatus.OK)
    public void defineProduct(@RequestBody DefineProductCommand defineProductCommand) throws JsonProcessingException {
        log.info("Got request to define product.");
        // Todo: Validate the request (Required args present, etc)
        productService.handle(defineProductCommand);
    }

    @PostMapping("/activate/{aggregateId}")
    @ResponseStatus(HttpStatus.OK)
    public void activateProduct(@PathVariable String aggregateId) throws JsonProcessingException {
        productService.handle(new ProductActivatedCommand(aggregateId));
    }

    @PostMapping("/deactivate/{aggregateId}")
    @ResponseStatus(HttpStatus.OK)
    public void deactivateProduct(@PathVariable String aggregateId) throws JsonProcessingException {
        productService.handle(new ProductDeactivatedCommand(aggregateId));
    }
}

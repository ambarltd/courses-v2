package cloud.ambar.product.management.controllers;

import cloud.ambar.product.management.commands.ProductManagementCommandService;
import cloud.ambar.product.management.commands.models.commands.DefineCreditCardProductCommand;
import cloud.ambar.product.management.commands.models.commands.ActivateCreditCardProductCommand;
import cloud.ambar.product.management.commands.models.commands.DeactivateCreditCardProductCommand;
import cloud.ambar.product.management.commands.models.commands.ModifyCreditCardCommand;
import com.fasterxml.jackson.core.JsonProcessingException;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.PatchMapping;
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
public class ManagementCommandController {

    private final ProductManagementCommandService productService;

    @Autowired
    public ManagementCommandController(final ProductManagementCommandService productService) {
        this.productService = productService;
    }

    @PostMapping
    @ResponseStatus(HttpStatus.OK)
    public void defineProduct(@RequestBody DefineCreditCardProductCommand command) throws JsonProcessingException {
        productService.handle(command);
    }

    @PostMapping("/activate/{aggregateId}")
    @ResponseStatus(HttpStatus.OK)
    public void activateProduct(@PathVariable final String aggregateId) throws JsonProcessingException {
        productService.handle(new ActivateCreditCardProductCommand(aggregateId));
    }

    @PostMapping("/deactivate/{aggregateId}")
    @ResponseStatus(HttpStatus.OK)
    public void deactivateProduct(@PathVariable final String aggregateId) throws JsonProcessingException {
        productService.handle(new DeactivateCreditCardProductCommand(aggregateId));
    }

    // Todo: Add new URI Mapping to handle ModifyCreditCardColorCommands
    // PATCH '/api/v1/credit_card_product/product'
    @PatchMapping
    @ResponseStatus(HttpStatus.OK)
    public void modifyProduct(@RequestBody ModifyCreditCardCommand defineProductCommand) throws JsonProcessingException {
        productService.handle(defineProductCommand);
    }
}

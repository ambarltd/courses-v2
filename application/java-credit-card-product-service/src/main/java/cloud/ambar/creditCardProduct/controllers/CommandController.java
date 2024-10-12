package cloud.ambar.creditCardProduct.controllers;

import cloud.ambar.creditCardProduct.command.CreditCardProductCommandService;
import cloud.ambar.creditCardProduct.command.models.commands.DefineCreditCardProductCommand;
import cloud.ambar.creditCardProduct.command.models.commands.ActivateCreditCardProductCommand;
import cloud.ambar.creditCardProduct.command.models.commands.DeactivateCreditCardProductCommand;
import cloud.ambar.creditCardProduct.command.models.commands.ModifyCreditCardCommand;
import cloud.ambar.creditCardProduct.command.models.validation.HexColorValidator;
import cloud.ambar.creditCardProduct.command.models.validation.PaymentCycle;
import cloud.ambar.creditCardProduct.command.models.validation.RewardsType;
import cloud.ambar.creditCardProduct.exceptions.InvalidPaymentCycleException;
import cloud.ambar.creditCardProduct.exceptions.InvalidRewardException;
import com.fasterxml.jackson.core.JsonProcessingException;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseStatus;

import java.util.Arrays;

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

    private final CreditCardProductCommandService productService;

    @Autowired
    public CommandController(final CreditCardProductCommandService productService) {
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

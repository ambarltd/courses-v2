package cloud.ambar.product.management.util;

import cloud.ambar.product.management.commands.ProductManagementCommandService;
import cloud.ambar.product.management.commands.models.ActivateCreditCardProductCommand;
import cloud.ambar.product.management.commands.models.DeactivateCreditCardProductCommand;
import cloud.ambar.product.management.projection.models.CreditCardProduct;
import cloud.ambar.product.management.query.ProductManagementQueryService;
import com.fasterxml.jackson.core.JsonProcessingException;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.boot.ApplicationArguments;
import org.springframework.boot.ApplicationRunner;
import org.springframework.stereotype.Component;

import java.util.List;
import java.util.Random;
import java.util.concurrent.TimeUnit;

import static cloud.ambar.product.management.util.Constants.BASIC;
import static cloud.ambar.product.management.util.Constants.STARTER;
import static cloud.ambar.product.management.util.Constants.BASIC_CASH_BACK;
import static cloud.ambar.product.management.util.Constants.BASIC_POINTS;
import static cloud.ambar.product.management.util.Constants.PLATINUM;

/**
 * This is just a simple component which on startup of the application will try to define a few initial cards. This is
 * just so that we have something to play with in our application.
 */
@Component
@RequiredArgsConstructor
public class DefaultCardCreator implements ApplicationRunner {
    private static final Logger log = LogManager.getLogger(DefaultCardCreator.class);

    private final ProductManagementCommandService commandService;

    private final ProductManagementQueryService queryService;

    @Override
    public void run(ApplicationArguments args) throws Exception {
        log.info("Defining initial card products");
        sleep(15000);

        commandService.handle(BASIC);
        commandService.handle(BASIC_CASH_BACK);
        commandService.handle(BASIC_POINTS);

        log.info("Listing all cards to randomly activate and deactivate them...!");
        List<CreditCardProduct> products = queryService.getAllCreditCardProducts();

        Random random = new Random();
        // Generate a random duration between 5 and 10 minutes (in milliseconds)
        long minMillis = TimeUnit.MINUTES.toMillis(5);
        long maxMillis = TimeUnit.MINUTES.toMillis(10);
        long randomMillis;

        products.forEach(p -> {
            if (!p.isActive()) {
                try {
                    commandService.handle(new ActivateCreditCardProductCommand(p.getId()));
                } catch (JsonProcessingException e) {
                    throw new RuntimeException(e);
                }
            }
        });

        while (true) {
            products = queryService.getAllCreditCardProducts();
            randomMillis = minMillis + (long) (random.nextDouble() * (maxMillis - minMillis));
            sleep(randomMillis);

            CreditCardProduct card = products.get(random.nextInt(products.size()));
            log.info("Selected card " + card.getName());
            log.info("Flipping active status.");
            if (card.isActive()) {
                try {
                    commandService.handle(new DeactivateCreditCardProductCommand(card.getId()));
                } catch (Exception e) {
                    // We might get an exception back if it was the last active card. We can just move on.
                }
            } else {
                commandService.handle(new ActivateCreditCardProductCommand(card.getId()));
            }
        }
    }
    private void sleep(long millis) {
        log.info("Sleeping for " + millis + " milliseconds.");
        try {
            // Sleep to give time for the db to init.
            Thread.sleep(millis);
        } catch (InterruptedException e) {
            // Do nothing.
        }
    }
}

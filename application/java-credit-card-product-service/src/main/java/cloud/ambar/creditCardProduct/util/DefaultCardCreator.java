package cloud.ambar.creditCardProduct.util;

import cloud.ambar.creditCardProduct.command.CreditCardProductCommandService;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.boot.ApplicationArguments;
import org.springframework.boot.ApplicationRunner;
import org.springframework.stereotype.Component;

import static cloud.ambar.creditCardProduct.util.Constants.BASIC;
import static cloud.ambar.creditCardProduct.util.Constants.STARTER;
import static cloud.ambar.creditCardProduct.util.Constants.BASIC_CASH_BACK;
import static cloud.ambar.creditCardProduct.util.Constants.BASIC_POINTS;
import static cloud.ambar.creditCardProduct.util.Constants.PLATINUM;

/**
 * This is just a simple component which on startup of the application will try to define a few initial cards. This is
 * just so that we have something to play with in our application.
 */
@Component
@RequiredArgsConstructor
public class DefaultCardCreator implements ApplicationRunner {
    private static final Logger log = LogManager.getLogger(DefaultCardCreator.class);

    private final CreditCardProductCommandService commandService;

    @Override
    public void run(ApplicationArguments args) throws Exception {
        log.info("Defining initial card products");
        commandService.handle(STARTER);
        commandService.handle(BASIC);
        commandService.handle(BASIC_CASH_BACK);
        commandService.handle(BASIC_POINTS);
        commandService.handle(PLATINUM);
    }
}

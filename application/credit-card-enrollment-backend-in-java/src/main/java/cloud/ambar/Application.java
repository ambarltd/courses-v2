package cloud.ambar;

import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;

@SpringBootApplication(scanBasePackages = "cloud.ambar")
public class Application {
    private static final Logger log = LogManager.getLogger(Application.class);

    /**
     * Spring Application which will run a webserver hosting our EventSourcing java application.
     * On startup, this application will
     * 1. Validate any tables for EventStorage / Projection & Reaction are created
     * 2. Start the application and make it available.
     * @param args
     */
    public static void main(String[] args) {
        log.info("Starting up main application");

        SpringApplication.run(Application.class, args);
    }
}

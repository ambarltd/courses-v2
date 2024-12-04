package cloud.ambar.common.projection;

import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.boot.ApplicationRunner;
import org.springframework.context.annotation.Bean;
import org.springframework.stereotype.Component;

@Component
public class MongoInitializer {
    private static final Logger log = LogManager.getLogger(MongoInitializer.class);

    private final MongoTransactionalAPI mongoTransactionalAPI;

    public MongoInitializer(MongoTransactionalAPI mongoTransactionalAPI) {
        this.mongoTransactionalAPI = mongoTransactionalAPI;
    }

    @Bean
    ApplicationRunner initMongo() {
        return args -> {
            // Create collections
            log.info("Creating collections");
            mongoTransactionalAPI.operate().createCollection("CreditCard_Enrollment_ProductName");
            mongoTransactionalAPI.operate().createCollection("CreditCard_Enrollment_ProductActiveStatus");

            // Create indexes
            log.info("Creating indexese");
            mongoTransactionalAPI.operate().indexOps("CreditCard_Enrollment")
                    .ensureIndex(new org.springframework.data.mongodb.core.index.Index().on("userId", org.springframework.data.domain.Sort.Direction.ASC));
        };
    }
}
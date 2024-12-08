package cloud.ambar.common.projection;

import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.boot.ApplicationRunner;
import org.springframework.context.annotation.Bean;
import org.springframework.data.mongodb.core.index.Index;
import org.springframework.stereotype.Component;

@Component
public class MongoInitializer {
    private static final Logger log = LogManager.getLogger(MongoInitializer.class);

    private final MongoNonTransactionalApi mongoNonTransactionalApi;

    public MongoInitializer(MongoNonTransactionalApi mongoNonTransactionalApi) {
        this.mongoNonTransactionalApi = mongoNonTransactionalApi;
    }

    @Bean
    ApplicationRunner initMongo() {
        return args -> {
            log.info("Creating collections");
            mongoNonTransactionalApi.operate().createCollection("CreditCard_Enrollment_Enrollment");
            mongoNonTransactionalApi.operate().createCollection("CreditCard_Enrollment_ProductName");
            mongoNonTransactionalApi.operate().createCollection("CreditCard_Enrollment_ProductActiveStatus");
            log.info("Created collections");

            log.info("Creating indexes");
            mongoNonTransactionalApi.operate().indexOps("CreditCard_Enrollment_Enrollment")
                    .ensureIndex(new Index().on("userId", org.springframework.data.domain.Sort.Direction.ASC));
            log.info("Created indexes");
        };
    }
}
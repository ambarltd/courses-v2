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

    private final MongoInitializerApi mongoInitializerApi;

    public MongoInitializer(MongoInitializerApi mongoInitializerApi) {
        this.mongoInitializerApi = mongoInitializerApi;
    }

    @Bean
    ApplicationRunner initMongo() {
        return args -> {
            log.info("Creating collections");
            mongoInitializerApi.operate().createCollection("CreditCard_Enrollment_Enrollment");
            mongoInitializerApi.operate().createCollection("CreditCard_Enrollment_ProductName");
            mongoInitializerApi.operate().createCollection("CreditCard_Enrollment_ProductActiveStatus");
            log.info("Created collections");

            log.info("Creating indexese");
            mongoInitializerApi.operate().indexOps("CreditCard_Enrollment")
                    .ensureIndex(new Index().on("userId", org.springframework.data.domain.Sort.Direction.ASC));
            log.info("Created indexes");
        };
    }
}
package cloud.ambar.common.util;

import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.data.mongodb.core.MongoTemplate;
import org.springframework.data.mongodb.core.index.Index;

@RequiredArgsConstructor
public class MongoInitializerApi {
    private static final Logger log = LogManager.getLogger(MongoInitializerApi.class);

    private final MongoTemplate mongoTemplate;

    public void initialize() {
        log.info("Creating collections");
        mongoTemplate.createCollection("CreditCard_Enrollment_Enrollment");
        mongoTemplate.createCollection("CreditCard_Enrollment_ProductName");
        mongoTemplate.createCollection("CreditCard_Enrollment_ProductActiveStatus");
        log.info("Created collections");

        log.info("Creating indexes");
        mongoTemplate.indexOps("CreditCard_Enrollment_Enrollment")
                .ensureIndex(new Index().on("userId", org.springframework.data.domain.Sort.Direction.ASC));
        log.info("Created indexes");
    }
}

package cloud.ambar.common.projection;

import lombok.RequiredArgsConstructor;
import org.springframework.data.mongodb.core.MongoTemplate;

@RequiredArgsConstructor
public class MongoInitializerApi {
    private final MongoTemplate mongoTemplate;

    public MongoTemplate operate() {
        return mongoTemplate;
    }
}

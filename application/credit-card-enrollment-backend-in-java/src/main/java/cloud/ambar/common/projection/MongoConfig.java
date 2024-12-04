package cloud.ambar.common.projection;

import com.mongodb.client.ClientSession;
import com.mongodb.client.MongoClient;
import com.mongodb.client.MongoClients;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.data.mongodb.core.MongoTemplate;
import org.springframework.web.context.annotation.RequestScope;


@Configuration
public class MongoConfig {
    @Value("${app.mongodb.transactional-api.uri}")
    private String mongodbUri;

    @Value("${app.mongodb.transactional-api.database}")
    private String mongoDatabaseName;

    @Bean
    @RequestScope
    public MongoTransactionalAPI mongoTransactionalAPI() {
        MongoClient mongoClient = MongoClients.create(mongodbUri);
        ClientSession session = mongoClient.startSession();
        MongoTemplate mongoTemplate = new MongoTemplate(mongoClient, mongoDatabaseName).withSession(session);

        return new MongoTransactionalAPI(mongoTemplate, session);
    }

    @Bean
    public MongoInitializerApi mongoInitializerApi() {
        MongoClient mongoClient = MongoClients.create(mongodbUri);
        MongoTemplate mongoTemplate = new MongoTemplate(mongoClient, mongoDatabaseName);

        return new MongoInitializerApi(mongoTemplate);
    }
}


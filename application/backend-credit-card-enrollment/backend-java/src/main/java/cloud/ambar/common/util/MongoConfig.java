package cloud.ambar.common.util;

import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import com.mongodb.ConnectionString;
import com.mongodb.MongoClientSettings;
import com.mongodb.client.ClientSession;
import com.mongodb.client.MongoClient;
import com.mongodb.client.MongoClients;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.context.annotation.Lazy;
import org.springframework.data.mongodb.core.MongoTemplate;
import org.springframework.data.mongodb.core.convert.DefaultMongoTypeMapper;
import org.springframework.data.mongodb.core.convert.MappingMongoConverter;
import org.springframework.web.context.annotation.RequestScope;

import java.util.concurrent.TimeUnit;

@Configuration
public class MongoConfig {
    @Value("${app.mongodb.uri}")
    private String mongodbUri;

    @Value("${app.mongodb.database}")
    private String mongoDatabaseName;

    private static final Logger log = LogManager.getLogger(MongoConfig.class);

    @Bean("MongoClientForTransactionalSupport")
    public MongoClient mongoClientForTransactionalSupport() {
        ConnectionString connectionString = new ConnectionString(mongodbUri);
        MongoClientSettings settings = MongoClientSettings.builder()
                .applyConnectionString(connectionString)
                .applyToConnectionPoolSettings(builder ->
                        builder.maxSize(20)
                                .minSize(5)
                                .maxWaitTime(2000, TimeUnit.MILLISECONDS)
                                .maxConnectionLifeTime(30, TimeUnit.MINUTES)
                                .maxConnectionIdleTime(10, TimeUnit.MINUTES)
                )
                .build();

        return MongoClients.create(settings);
    }

    @Bean("MongoClientForNonTransactionalOperations")
    public MongoClient mongoClientForNonTransactionalOperations() {
        ConnectionString connectionString = new ConnectionString(mongodbUri);
        MongoClientSettings settings = MongoClientSettings.builder()
                .applyConnectionString(connectionString)
                .build();

        return MongoClients.create(settings);
    }

    // It's extremely important to lazily initialize this bean. Why?
    // Because the session must be closed each time, so anyone who asks for this bean must either close
    // it explicitly or rely on something else closing it explicitly (such as the controller).
    // Why must it be closed? Because we might run out of slots in the pool.
    // If we didn't initalize it lazily, requests that don't need this bean would still create a session.
    @Bean
    @Lazy
    @RequestScope
    public MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator(
            @Qualifier("MongoClientForNonTransactionalOperations") MongoClient mongoClient
    ) {
        log.info("MongoClientForNonTransactionalOperations: Creating new session.");
        ClientSession session = mongoClient.startSession();
        MongoTemplate mongoTemplate = new MongoTemplate(mongoClient, mongoDatabaseName).withSession(session);

        // Disable _class field in mongo documents
        MappingMongoConverter converter = (MappingMongoConverter) mongoTemplate.getConverter();
        converter.setTypeMapper(new DefaultMongoTypeMapper(null));

        return new MongoTransactionalProjectionOperator(mongoTemplate, session);
    }

    @Bean
    public MongoInitializerApi mongoInitializerApi(
            @Qualifier("MongoClientForNonTransactionalOperations") MongoClient mongoClient
    ) {
        MongoTemplate mongoTemplate = new MongoTemplate(mongoClient, mongoDatabaseName);

        // Disable _class field in mongo documents
        MappingMongoConverter converter = (MappingMongoConverter) mongoTemplate.getConverter();
        converter.setTypeMapper(new DefaultMongoTypeMapper(null));

        return new MongoInitializerApi(mongoTemplate);
    }
}


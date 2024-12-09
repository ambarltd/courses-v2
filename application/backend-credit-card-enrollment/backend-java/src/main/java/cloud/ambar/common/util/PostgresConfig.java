package cloud.ambar.common.util;

import cloud.ambar.common.eventstore.PostgresTransactionalEventStore;
import cloud.ambar.common.serializedevent.Deserializer;
import cloud.ambar.common.serializedevent.Serializer;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.web.context.annotation.RequestScope;

import javax.sql.DataSource;
import java.sql.Connection;
import java.sql.SQLException;
import com.zaxxer.hikari.HikariConfig;
import com.zaxxer.hikari.HikariDataSource;

@Configuration
public class PostgresConfig {
    @Value("${app.postgresql.uri}")
    private String postgresUri;

    @Value("${app.postgresql.database}")
    private String postgresDatabase;

    @Value("${app.postgresql.table}")
    private String postgresTable;

    @Value("${app.postgresql.eventStoreCreateReplicationUserWithUsername}")
    private String postgresReplicationUsername;

    @Value("${app.postgresql.eventStoreCreateReplicationUserWithPassword}")
    private String postgresReplicationPassword;

    @Value("${app.postgresql.eventStoreCreateReplicationPublication}")
    private String postgresReplicationPublicationName;

    @Bean
    @Qualifier("DataSourceForTransactionalSupport")
    public DataSource dataSourceForTransactionalSupport() {
        HikariConfig config = new HikariConfig();
        config.setJdbcUrl(postgresUri);
        config.setAutoCommit(false);  // Important for transaction control

        config.setMaximumPoolSize(10);
        config.setMinimumIdle(5);
        config.setIdleTimeout(300000); // 5 minutes
        config.setConnectionTimeout(20000); // 20 seconds

        return new HikariDataSource(config);
    }

    @Bean
    @Qualifier("DataSourceForNonTransactionalOperations")
    public DataSource dataSourceNonTransactionalOperations() {
        HikariConfig config = new HikariConfig();
        config.setJdbcUrl(postgresUri);

        config.setMaximumPoolSize(10);
        config.setMinimumIdle(5);
        config.setIdleTimeout(300000);
        config.setConnectionTimeout(20000);

        return new HikariDataSource(config);
    }

    @Bean
    @RequestScope
    public PostgresTransactionalEventStore postgresTransactionalAPI(
            @Qualifier("DataSourceForTransactionalSupport") DataSource dataSource,
            Serializer serializer,
            Deserializer deserializer
    )  {
        try {
            Connection connection = dataSource.getConnection();
            return new PostgresTransactionalEventStore(connection, serializer, deserializer, postgresTable);
        } catch (SQLException e) {
            throw new RuntimeException("Failed to get connection from data source for PG transactional API", e);
        }
    }

    @Bean
    public PostgresInitializerApi postgresInitializerApi(
            @Qualifier("DataSourceForNonTransactionalOperations") DataSource dataSource
    ) {
        try {
            Connection connection = dataSource.getConnection();
            return new PostgresInitializerApi(
                    connection,
                    postgresDatabase,
                    postgresTable,
                    postgresReplicationUsername,
                    postgresReplicationPassword,
                    postgresReplicationPublicationName
            );
        } catch (SQLException e) {
            throw new RuntimeException("Failed to get connection for PG non transactional API", e);
        }
    }
}
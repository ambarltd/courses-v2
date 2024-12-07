package cloud.ambar.common.eventstore;

import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.boot.ApplicationRunner;
import org.springframework.context.annotation.Bean;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.stereotype.Component;

/**
 * By making this a component, we tell Spring to initialize this class and make it available to the Application Context
 * by doing this, along with ApplicationRunner bean, we can have this code run on startup of the application and ensure
 * that our event store is ready for us.
 */
@Component
public class EventStoreInitializer {
    private static final Logger log = LogManager.getLogger(EventStoreInitializer.class);

    private final JdbcTemplate jdbcTemplate;

    @Value("${EVENT_STORE_CREATE_TABLE_WITH_NAME}")
    private String eventStoreTableName;

    @Value("${EVENT_STORE_CREATE_REPLICATION_USER_WITH_USERNAME}")
    private String eventStoreCreateReplicationUserWithUsername;

    @Value("${EVENT_STORE_CREATE_REPLICATION_USER_WITH_PASSWORD}")
    private String eventStoreCreateReplicationUserWithPassword;

    @Value("${EVENT_STORE_DATABASE_NAME}")
    private String eventStoreDatabaseName;

    @Value("${EVENT_STORE_CREATE_REPLICATION_PUBLICATION}")
    private String eventStoreCreateReplicationPublication;

    public EventStoreInitializer(JdbcTemplate jdbcTemplate) {
        this.jdbcTemplate = jdbcTemplate;
    }

    @Bean
    ApplicationRunner initEventStore() {
        return args -> {
            // Create table
            log.info("Creating table " + eventStoreTableName);
            executeStatementIgnoreErrors(
                    String.format("""
                        CREATE TABLE IF NOT EXISTS %s (
                        id BIGSERIAL NOT NULL,
                        event_id TEXT NOT NULL UNIQUE,
                        aggregate_id TEXT NOT NULL,
                        aggregate_version BIGINT NOT NULL,
                        causation_id TEXT NOT NULL,
                        correlation_id TEXT NOT NULL,
                        recorded_on TEXT NOT NULL,
                        event_name TEXT NOT NULL,
                        json_payload TEXT NOT NULL,
                        json_metadata TEXT NOT NULL,
                        PRIMARY KEY (id));""",
                        eventStoreTableName)
            );

            // Create user
            log.info("Creating replication user");
            executeStatementIgnoreErrors(String.format(
                    "CREATE USER %s REPLICATION LOGIN PASSWORD '%s';",
                    eventStoreCreateReplicationUserWithUsername,
                    eventStoreCreateReplicationUserWithPassword
            ));

            // Grant permissions to user
            log.info("Granting permissions to replication user");
            executeStatementIgnoreErrors(String.format(
                    "GRANT CONNECT ON DATABASE \"%s\" TO %s;",
                    eventStoreDatabaseName,
                    eventStoreCreateReplicationUserWithUsername
            ));

            log.info("Granting select to replication user");
            executeStatementIgnoreErrors(String.format(
                    "GRANT SELECT ON TABLE %s TO %s;",
                    eventStoreTableName,
                    eventStoreCreateReplicationUserWithUsername
            ));

            // Create publication
            log.info("Creating publication for table");
            executeStatementIgnoreErrors(String.format(
                    "CREATE PUBLICATION %s FOR TABLE %s;",
                    eventStoreCreateReplicationPublication,
                    eventStoreTableName
            ));

            // Create indices
            log.info("Creating aggregate id, aggregate version index");
            executeStatementIgnoreErrors(String.format(
                    "CREATE UNIQUE INDEX event_store_idx_event_aggregate_id_version ON %s(aggregate_id, aggregate_version);",
                    eventStoreTableName
            ));
            log.info("Creating id index");
            executeStatementIgnoreErrors(String.format(
                    "CREATE UNIQUE INDEX event_store_idx_event_id ON %s(event_id);",
                    eventStoreTableName
            ));
            log.info("Creating causation index");
            executeStatementIgnoreErrors(String.format(
                    "CREATE INDEX event_store_idx_event_causation_id ON %s(causation_id);",
                    eventStoreTableName
            ));
            log.info("Creating correlation index");
            executeStatementIgnoreErrors(String.format(
                    "CREATE INDEX event_store_idx_event_correlation_id ON %s(correlation_id);",
                    eventStoreTableName
            ));
            log.info("Creating recording index");
            executeStatementIgnoreErrors(String.format(
                    "CREATE INDEX event_store_idx_occurred_on ON %s(recorded_on);",
                    eventStoreTableName
            ));
            log.info("Creating event name index");
            executeStatementIgnoreErrors(String.format(
                    "CREATE INDEX event_store_idx_event_name ON %s(event_name);",
                    eventStoreTableName
            ));
        };
    }

    private void executeStatementIgnoreErrors(final String sqlStatement) {
        try {
            log.info("Executing SQL: " + sqlStatement);
            jdbcTemplate.execute(sqlStatement);
        } catch (Exception e) {
            log.warn("Caught exception when executing SQL statement.");
            log.warn(e);
        }
    }
}

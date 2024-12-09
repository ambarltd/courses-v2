package cloud.ambar.common.util;

import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.context.annotation.Bean;

import java.sql.*;

@RequiredArgsConstructor
public class PostgresInitializerApi {
    private static final Logger log = LogManager.getLogger(PostgresInitializerApi.class);

    private final Connection connection;

    private final String eventStoreDatabaseName;

    private final String eventStoreTable;

    private final String eventStoreCreateReplicationUserWithUsername;

    private final String eventStoreCreateReplicationUserWithPassword;

    private final String eventStoreCreateReplicationPublication;

    @Bean
    public void initialize() {
        // Create table
        log.info("Creating table " + eventStoreTable);
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
                        eventStoreTable)
        );

        // Create replication user
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
                eventStoreTable,
                eventStoreCreateReplicationUserWithUsername
        ));

        // Create publication
        log.info("Creating publication for table");
        executeStatementIgnoreErrors(String.format(
                "CREATE PUBLICATION %s FOR TABLE %s;",
                eventStoreCreateReplicationPublication,
                eventStoreTable
        ));

        // Create indexes
        log.info("Creating aggregate id, aggregate version index");
        executeStatementIgnoreErrors(String.format(
                "CREATE UNIQUE INDEX event_store_idx_event_aggregate_id_version ON %s(aggregate_id, aggregate_version);",
                eventStoreTable
        ));
        log.info("Creating id index");
        executeStatementIgnoreErrors(String.format(
                "CREATE UNIQUE INDEX event_store_idx_event_id ON %s(event_id);",
                eventStoreTable
        ));
        log.info("Creating causation index");
        executeStatementIgnoreErrors(String.format(
                "CREATE INDEX event_store_idx_event_causation_id ON %s(causation_id);",
                eventStoreTable
        ));
        log.info("Creating correlation index");
        executeStatementIgnoreErrors(String.format(
                "CREATE INDEX event_store_idx_event_correlation_id ON %s(correlation_id);",
                eventStoreTable
        ));
        log.info("Creating recording index");
        executeStatementIgnoreErrors(String.format(
                "CREATE INDEX event_store_idx_occurred_on ON %s(recorded_on);",
                eventStoreTable
        ));
        log.info("Creating event name index");
        executeStatementIgnoreErrors(String.format(
                "CREATE INDEX event_store_idx_event_name ON %s(event_name);",
                eventStoreTable
        ));
    }

    private void executeStatementIgnoreErrors(final String sqlStatement) {
        try {
            log.info("Executing SQL: " + sqlStatement);
            connection.createStatement().execute(sqlStatement);
        } catch (Exception e) {
            log.warn("Caught exception when executing SQL statement.");
            log.warn(e);
        }
    }
}
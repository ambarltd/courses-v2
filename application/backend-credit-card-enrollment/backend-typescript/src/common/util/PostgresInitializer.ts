import { injectable, inject } from 'tsyringe';
import { PostgresConnectionPool } from './PostgresConnectionPool';
import { log } from './Logger';

@injectable()
export class PostgresInitializer {
    constructor(
        @inject(PostgresConnectionPool) private readonly connectionPool: PostgresConnectionPool,
        @inject("eventStoreDatabaseName") private readonly eventStoreDatabaseName: string,
        @inject("eventStoreTable") private readonly eventStoreTable: string,
        @inject("eventStoreCreateReplicationUserWithUsername") private readonly replicationUsername: string,
        @inject("eventStoreCreateReplicationUserWithPassword") private readonly replicationPassword: string,
        @inject("eventStoreCreateReplicationPublication") private readonly replicationPublication: string,
    ) {}

    async initialize(): Promise<void> {
        const client = await this.connectionPool.openConnection();

        try {
            // Create table
            log.info(`Creating table ${this.eventStoreTable}`);
            await this.executeStatementIgnoreErrors(client, `
                CREATE TABLE IF NOT EXISTS ${this.eventStoreTable} (
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
                    PRIMARY KEY (id)
                );
            `);

            // Create replication user
            log.info('Creating replication user');
            await this.executeStatementIgnoreErrors(client,
                `CREATE USER ${this.replicationUsername} REPLICATION LOGIN PASSWORD '${this.replicationPassword}';`
            );

            // Grant permissions to user
            log.info('Granting permissions to replication user');
            await this.executeStatementIgnoreErrors(client,
                `GRANT CONNECT ON DATABASE "${this.eventStoreDatabaseName}" TO ${this.replicationUsername};`
            );

            log.info('Granting select to replication user');
            await this.executeStatementIgnoreErrors(client,
                `GRANT SELECT ON TABLE ${this.eventStoreTable} TO ${this.replicationUsername};`
            );

            // Create publication
            log.info('Creating publication for table');
            await this.executeStatementIgnoreErrors(client,
                `CREATE PUBLICATION ${this.replicationPublication} FOR TABLE ${this.eventStoreTable};`
            );

            // Create indexes
            log.info('Creating aggregate id, aggregate version index');
            await this.executeStatementIgnoreErrors(client,
                `CREATE UNIQUE INDEX event_store_idx_event_aggregate_id_version ON ${this.eventStoreTable}(aggregate_id, aggregate_version);`
            );

            log.info('Creating id index');
            await this.executeStatementIgnoreErrors(client,
                `CREATE UNIQUE INDEX event_store_idx_event_id ON ${this.eventStoreTable}(event_id);`
            );

            log.info('Creating causation index');
            await this.executeStatementIgnoreErrors(client,
                `CREATE INDEX event_store_idx_event_causation_id ON ${this.eventStoreTable}(causation_id);`
            );

            log.info('Creating correlation index');
            await this.executeStatementIgnoreErrors(client,
                `CREATE INDEX event_store_idx_event_correlation_id ON ${this.eventStoreTable}(correlation_id);`
            );

            log.info('Creating recording index');
            await this.executeStatementIgnoreErrors(client,
                `CREATE INDEX event_store_idx_occurred_on ON ${this.eventStoreTable}(recorded_on);`
            );

            log.info('Creating event name index');
            await this.executeStatementIgnoreErrors(client,
                `CREATE INDEX event_store_idx_event_name ON ${this.eventStoreTable}(event_name);`
            );

        } finally {
            client.release();
        }
    }

    private async executeStatementIgnoreErrors(client: any, sqlStatement: string): Promise<void> {
        try {
            log.info(`Executing SQL: ${sqlStatement}`);
            await client.query(sqlStatement);
        } catch (error) {
            log.warn('Caught exception when executing SQL statement.', error as Error);
        }
    }
}
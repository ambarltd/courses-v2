import { PoolClient } from 'pg';
import { PostgresConnectionPool } from '../util/PostgresConnectionPool';
import { Serializer } from '../serializedEvent/Serializer';
import { Deserializer } from '../serializedEvent/Deserializer';
import { SerializedEvent } from '../serializedEvent/SerializedEvent';
import { Event } from '../event/Event';
import { CreationEvent } from '../event/CreationEvent';
import { TransformationEvent } from '../event/TransformationEvent';
import { Aggregate } from '../aggregate/Aggregate';
import { log } from '../util/Logger';
import { AggregateAndEventIdsInLastEvent } from './AggregateAndEventIdsInLastEvent';


export class PostgresTransactionalEventStore {
    private connection: PoolClient | null = null;
    private activeTransaction = false;

    constructor(
        private readonly connectionPool: PostgresConnectionPool,
        private readonly serializer: Serializer,
        private readonly deserializer: Deserializer,
        private readonly eventStoreTable: string
    ) {}

    async beginTransaction(): Promise<void> {
        if (this.connection || this.activeTransaction) {
            throw new Error('Connection or transaction already active!');
        }

        try {
            this.connection = await this.connectionPool.openConnection();
            await this.connection.query('BEGIN ISOLATION LEVEL SERIALIZABLE');

            this.activeTransaction = true;
        } catch (error) {
            const maxLen = 500;
            const errorMessage = error instanceof Error ? error.message : String(error);
            throw new Error(
                'Failed to start transaction with ' +
                (errorMessage.length > maxLen ? errorMessage.substring(0, maxLen) : errorMessage)
            );
        }
    }

    async findAggregate<T extends Aggregate>(aggregateId: string): Promise<AggregateAndEventIdsInLastEvent<T>> {
        if (!this.activeTransaction) {
            throw new Error('Transaction must be active to perform operations!');
        }

        const serializedEvents = await this.findAllSerializedEventsByAggregateId(aggregateId);
        const events = serializedEvents.map(e => this.deserializer.deserialize(e));

        if (events.length === 0) {
            throw new Error(`No events found for aggregateId: ${aggregateId}`);
        }

        const creationEvent = events[0];
        const transformationEvents = events.slice(1);

        if (!this.isCreationEventForAggregate<T>(creationEvent)) {
            throw new Error('First event is not a creation event');
        }

        let aggregate = creationEvent.createAggregate();
        let eventIdOfLastEvent = creationEvent.eventId;
        let correlationIdOfLastEvent = creationEvent.correlationId;

        for (const transformationEvent of transformationEvents) {
            if (!this.isTransformationEventForAggregate<T>(transformationEvent)) {
                throw new Error('Event is not a transformation event');
            }
            aggregate = transformationEvent.transformAggregate(aggregate);
            eventIdOfLastEvent = transformationEvent.eventId;
            correlationIdOfLastEvent = transformationEvent.correlationId;
        }

        return {
            aggregate,
            eventIdOfLastEvent,
            correlationIdOfLastEvent
        };
    }

    async saveEvent(event: Event): Promise<void> {
        if (!this.activeTransaction) {
            throw new Error('Transaction must be active to perform operations!');
        }

        await this.saveSerializedEvent(this.serializer.serialize(event));
    }

    async doesEventAlreadyExist(eventId: string): Promise<boolean> {
        if (!this.activeTransaction) {
            throw new Error('Transaction must be active to perform operations!');
        }

        const event = await this.findSerializedEventByEventId(eventId);
        return event !== null;
    }

    async commitTransaction(): Promise<void> {
        if (!this.activeTransaction) {
            throw new Error('Transaction must be active to commit!');
        }

        try {
            await this.connection?.query('COMMIT');
            this.activeTransaction = false;
        } catch (error) {
            throw new Error(`Failed to commit transaction: ${error}`);
        }
    }

    async abortDanglingTransactionsAndReturnConnectionToPool(): Promise<void> {
        if (this.activeTransaction) {
            try {
                await this.connection?.query('ROLLBACK');
                this.activeTransaction = false;
            } catch (error) {
                log.error('Failed to rollback PG transaction', error as Error);
            }
        }

        if (this.connection) {
            try {
                this.connection.release();
                this.connection = null;
            } catch (error) {
                log.error('Failed to release PG connection', error as Error);
            }
        }
    }

    private async findAllSerializedEventsByAggregateId(aggregateId: string): Promise<SerializedEvent[]> {
        if (!this.connection) throw new Error('No active connection');

        const sql = `
            SELECT id, event_id, aggregate_id, causation_id, correlation_id, 
                   aggregate_version, json_payload, json_metadata, recorded_on, event_name
            FROM ${this.eventStoreTable}
            WHERE aggregate_id = $1 
            ORDER BY aggregate_version ASC
        `;

        try {
            const result = await this.connection.query(sql, [aggregateId]);
            return result.rows.map(this.mapRowToSerializedEvent);
        } catch (error) {
            throw new Error(`Failed to fetch events for aggregate: ${aggregateId}: ${error}`);
        }
    }

    private async saveSerializedEvent(serializedEvent: SerializedEvent): Promise<void> {
        if (!this.connection) throw new Error('No active connection');

        const sql = `
            INSERT INTO ${this.eventStoreTable} (
                event_id, aggregate_id, causation_id, correlation_id, 
                aggregate_version, json_payload, json_metadata, recorded_on, event_name
            ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
        `;

        const values = [
            serializedEvent.eventId,
            serializedEvent.aggregateId,
            serializedEvent.causationId,
            serializedEvent.correlationId,
            serializedEvent.aggregateVersion,
            serializedEvent.jsonPayload,
            serializedEvent.jsonMetadata,
            serializedEvent.recordedOn,
            serializedEvent.eventName
        ];

        try {
            await this.connection.query(sql, values);
        } catch (error) {
            throw new Error(`Failed to save event: ${serializedEvent.eventId}: ${error}`);
        }
    }

    private async findSerializedEventByEventId(eventId: string): Promise<SerializedEvent | null> {
        if (!this.connection) throw new Error('No active connection');

        const sql = `
            SELECT id, event_id, aggregate_id, causation_id, correlation_id, 
                   aggregate_version, json_payload, json_metadata, recorded_on, event_name
            FROM ${this.eventStoreTable}
            WHERE event_id = $1
        `;

        try {
            const result = await this.connection.query(sql, [eventId]);
            return result.rows.length > 0 ? this.mapRowToSerializedEvent(result.rows[0]) : null;
        } catch (error) {
            throw new Error(`Failed to fetch event: ${eventId}: ${error}`);
        }
    }

    private mapRowToSerializedEvent(row: any): SerializedEvent {
        return {
            id: row.id,
            eventId: row.event_id,
            aggregateId: row.aggregate_id,
            causationId: row.causation_id,
            correlationId: row.correlation_id,
            aggregateVersion: row.aggregate_version,
            jsonPayload: row.json_payload,
            jsonMetadata: row.json_metadata,
            recordedOn: row.recorded_on,
            eventName: row.event_name
        };
    }

    private isCreationEventForAggregate<T extends Aggregate>(event: Event): event is CreationEvent<T> {
        return event instanceof CreationEvent;
    }

    private isTransformationEventForAggregate<T extends Aggregate>(event: Event): event is TransformationEvent<T> {
        return event instanceof TransformationEvent;
    }
}
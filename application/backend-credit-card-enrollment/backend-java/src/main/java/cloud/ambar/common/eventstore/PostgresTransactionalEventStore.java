package cloud.ambar.common.eventstore;

import cloud.ambar.common.aggregate.Aggregate;
import cloud.ambar.common.event.CreationEvent;
import cloud.ambar.common.event.Event;
import cloud.ambar.common.event.TransformationEvent;
import cloud.ambar.common.queryhandler.QueryController;
import cloud.ambar.common.serializedevent.Deserializer;
import cloud.ambar.common.serializedevent.SerializedEvent;
import cloud.ambar.common.serializedevent.Serializer;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;
import java.util.Optional;

@RequiredArgsConstructor
public class PostgresTransactionalEventStore implements AutoCloseable {
    private static final Logger log = LogManager.getLogger(PostgresTransactionalEventStore.class);

    private final Connection connection;

    private final Serializer serializer;

    private final Deserializer deserializer;

    private final String eventStoreTable;

    private boolean isTransactionActive = false;

    public void beginTransaction() {
        if (isTransactionActive) {
            throw new RuntimeException("Transaction already active!");
        }
        try {
            connection.setAutoCommit(false);
            try (Statement stmt = connection.createStatement()) {
                stmt.execute("BEGIN TRANSACTION ISOLATION LEVEL SERIALIZABLE");
            }
            isTransactionActive = true;
        } catch (SQLException e) {
            throw new RuntimeException("Failed to start transaction", e);
        }
    }

    public AggregateAndEventIdsInLastEvent findAggregate(String aggregateId) {
        final List<SerializedEvent> serializedEvents = findAllSerializedEventsByAggregateId(aggregateId);
        final List<Event> events = serializedEvents.stream()
                .map(deserializer::deserialize)
                .toList();

        if (events.isEmpty()) {
            throw new RuntimeException("No events found for aggregateId: " + aggregateId);
        }

        final Event creationEvent = events.getFirst();
        final List<Event> transformationEvents = events.subList(1, events.size());

        Aggregate aggregate;
        if (creationEvent instanceof CreationEvent<?>) {
            aggregate = ((CreationEvent<Aggregate>) creationEvent).createAggregate();
        } else {
            throw new RuntimeException("First event is not a creation event");
        }

        String eventIdOfLastEvent = creationEvent.getEventId();
        String correlationIdOfLastEvent = creationEvent.getCorrelationId();

        for (Event transformationEvent : transformationEvents) {
            if (transformationEvent instanceof TransformationEvent<?>) {
                aggregate = ((TransformationEvent<Aggregate>) transformationEvent).transformAggregate(aggregate);
                eventIdOfLastEvent = transformationEvent.getEventId();
                correlationIdOfLastEvent = transformationEvent.getCorrelationId();

            } else {
                throw new RuntimeException("Event is not a transformation event");
            }
        }


        return new AggregateAndEventIdsInLastEvent(
                aggregate,
                eventIdOfLastEvent,
                correlationIdOfLastEvent
        );
    }

    public void saveEvent(Event event) {
        saveSerializedEvent(serializer.serialize(event));
    }

    public boolean doesEventAlreadyExist(String eventId) {
        return findSerializedEventByEventId(eventId).isPresent();
    }

    public void commitTransaction() {
        if (!isTransactionActive) {
            throw new RuntimeException("Transaction must be active to commit!");
        }
        try {
            connection.commit();
            isTransactionActive = false;
        } catch (SQLException e) {
            throw new RuntimeException("Failed to commit transaction", e);
        }
    }

    public void abortTransaction() {
        if (!isTransactionActive) {
            throw new RuntimeException("Transaction must be active to abort!");
        }
        try {
            connection.rollback();
            isTransactionActive = false;
        } catch (SQLException e) {
            throw new RuntimeException("Failed to abort transaction", e);
        }
    }

    public boolean isTransactionActive() {
        return isTransactionActive;
    }

    // IMPLEMENTATION OF AutoCloseable INTERFACE - cleanly close dangling transactions
    // when the transaction event store gets garbage collected.
    // I.e., it will return the event store's connection back to the connection pool.
    // Note: There is need to close the connection, because that would mess with the library's connection pool.
    // The transactional event store is meant to be used in @RequestScope, so the connection will be cleaned up
    // by the library when the transactional event store and its connection are garbage collected.
    public void close() {
        if (isTransactionActive) {
            abortTransaction();
        }
    }

    private List<SerializedEvent> findAllSerializedEventsByAggregateId(String aggregateId) {
        if (!isTransactionActive) {
            throw new RuntimeException("Transaction must be active to perform operations!");
        }

        List<SerializedEvent> events = new ArrayList<>();
        String sql = String.format("""
            SELECT id, event_id, aggregate_id, causation_id, correlation_id, 
                   aggregate_version, json_payload, json_metadata, recorded_on, event_name
            FROM %s
            WHERE aggregate_id = ? 
            ORDER BY aggregate_version ASC
            """, eventStoreTable);

        try (PreparedStatement stmt = connection.prepareStatement(sql)) {
            stmt.setString(1, aggregateId);
            ResultSet rs = stmt.executeQuery();

            while (rs.next()) {
                events.add(mapResultSetToSerializedEvent(rs));
            }
            return events;
        } catch (SQLException e) {
            throw new RuntimeException("Failed to fetch events for aggregate: " + aggregateId, e);
        }
    }

    private void saveSerializedEvent(SerializedEvent event) {
        if (!isTransactionActive) {
            throw new RuntimeException("Transaction must be active to perform operations!");
        }

        String sql = String.format("""
            INSERT INTO %s (
                event_id, aggregate_id, causation_id, correlation_id, 
                aggregate_version, json_payload, json_metadata, recorded_on, event_name
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            """, eventStoreTable);

        try (PreparedStatement stmt = connection.prepareStatement(sql)) {
            stmt.setString(1, event.getEventId());
            stmt.setString(2, event.getAggregateId());
            stmt.setString(3, event.getCausationId());
            stmt.setString(4, event.getCorrelationId());
            stmt.setInt(5, event.getAggregateVersion());
            stmt.setString(6, event.getJsonPayload());
            stmt.setString(7, event.getJsonMetadata());
            stmt.setString(8, event.getRecordedOn());
            stmt.setString(9, event.getEventName());

            stmt.executeUpdate();
        } catch (SQLException e) {
            log.error(e.getClass());
            log.error(e.getMessage());
            log.error(e);
            throw new RuntimeException("Failed to save event: " + event.getEventId(), e);
        }
    }

    private Optional<SerializedEvent> findSerializedEventByEventId(String eventId) {
        if (!isTransactionActive) {
            throw new RuntimeException("Transaction must be active to perform operations!");
        }

        String sql = String.format("""
            SELECT id, event_id, aggregate_id, causation_id, correlation_id, 
                   aggregate_version, json_payload, json_metadata, recorded_on, event_name
            FROM %s
            WHERE event_id = ?
            """, eventStoreTable);

        try (PreparedStatement stmt = connection.prepareStatement(sql)) {
            stmt.setString(1, eventId);
            ResultSet rs = stmt.executeQuery();

            if (rs.next()) {
                return Optional.of(mapResultSetToSerializedEvent(rs));
            }
            return Optional.empty();
        } catch (SQLException e) {
            throw new RuntimeException("Failed to fetch event: " + eventId, e);
        }
    }

    private SerializedEvent mapResultSetToSerializedEvent(ResultSet rs) throws SQLException {
        return SerializedEvent.builder()
                .id(rs.getInt("id"))
                .eventId(rs.getString("event_id"))
                .aggregateId(rs.getString("aggregate_id"))
                .causationId(rs.getString("causation_id"))
                .correlationId(rs.getString("correlation_id"))
                .aggregateVersion(rs.getInt("aggregate_version"))
                .jsonPayload(rs.getString("json_payload"))
                .jsonMetadata(rs.getString("json_metadata"))
                .recordedOn(rs.getString("recorded_on"))
                .eventName(rs.getString("event_name"))
                .build();
    }
}
using CreditCardEnrollment.Common.SerializedEvent;
using Npgsql;
using CreditCardEnrollment.Common.Event;
using CreditCardEnrollment.Common.Util;

namespace CreditCardEnrollment.Common.EventStore;

public class PostgresTransactionalEventStore
{
    private readonly PostgresConnectionPool _connectionPool;
    private readonly Serializer _serializer;
    private readonly Deserializer _deserializer;
    private readonly string _eventStoreTable;
    private readonly ILogger<PostgresTransactionalEventStore> _logger;
    private NpgsqlConnection? _connection;
    private NpgsqlTransaction? _activeTransaction;

    public PostgresTransactionalEventStore(
        PostgresConnectionPool connectionPool,
        Serializer serializer,
        Deserializer deserializer,
        string eventStoreTable,
        ILogger<PostgresTransactionalEventStore> logger
    ) {
        _connectionPool = connectionPool;
        _serializer = serializer;
        _deserializer = deserializer;
        _eventStoreTable = eventStoreTable;
        _logger = logger;
        _connection = null;
        _activeTransaction = null;
    }

    public void BeginTransaction()
    {
        if (_connection != null || _activeTransaction != null) {
            throw new Exception("Connection or transaction already active!");
        }

        try {
            _connection = _connectionPool.OpenConnection();
            _activeTransaction = _connection.BeginTransaction(System.Data.IsolationLevel.Serializable);
        } catch (Exception ex) {
            const int max = 500;  
            throw new Exception("Failed to start transaction with " + 
                (ex.Message?.Length > max ? ex.Message.Substring(0, max) : ex.Message), ex);
        }
    }

    public AggregateAndEventIdsInLastEvent<T> FindAggregate<T>(string aggregateId) where T : Aggregate.Aggregate
    {
        if (_activeTransaction == null) {
            throw new Exception("Transaction must be active to perform operations!");
        }
        
        var serializedEvents = FindAllSerializedEventsByAggregateId(aggregateId);
        var events = serializedEvents.Select(e => _deserializer.Deserialize(e)).ToList();

        if (!events.Any()) {
            throw new Exception($"No events found for aggregateId: {aggregateId}");
        }

        var creationEvent = events.First();
        var transformationEvents = events.Skip(1).ToList();

        T aggregate;
        if (creationEvent is CreationEvent<T> creation) {
            aggregate = creation.CreateAggregate();
        } else {
            throw new Exception("First event is not a creation event");
        }

        string eventIdOfLastEvent = creationEvent.EventId;
        string correlationIdOfLastEvent = creationEvent.CorrelationId;

        foreach (var transformationEvent in transformationEvents) {
            if (transformationEvent is TransformationEvent<T> transformation) {
                aggregate = transformation.TransformAggregate(aggregate);
                eventIdOfLastEvent = transformationEvent.EventId;
                correlationIdOfLastEvent = transformationEvent.CorrelationId;
            } else {
                throw new Exception("Event is not a transformation event");
            }
        }

        return new AggregateAndEventIdsInLastEvent<T> {
            Aggregate = aggregate,
            EventIdOfLastEvent = eventIdOfLastEvent,
            CorrelationIdOfLastEvent = correlationIdOfLastEvent
        };
    }

    public void SaveEvent(Event.Event @event)
    {
        if (_activeTransaction == null) {
            throw new Exception("Transaction must be active to perform operations!");
        }
        
        SaveSerializedEvent(_serializer.Serialize(@event));
    }

    public bool DoesEventAlreadyExist(string eventId)
    {
        if (_activeTransaction == null) {
            throw new Exception("Transaction must be active to perform operations!");
        }
        
        return FindSerializedEventByEventId(eventId) != null;
    }

    public void CommitTransaction()
    {
        if (_activeTransaction == null) {
            throw new Exception("Transaction must be active to commit!");
        }
        try {
            _activeTransaction.Commit();
            _activeTransaction = null;
        } catch (Exception ex) {
            throw new Exception("Failed to commit transaction", ex);
        }
    }

    public void AbortDanglingTransactionsAndReturnConnectionToPool()
    {
        if (_activeTransaction != null) {
            try {
                _activeTransaction.Rollback();
                _activeTransaction = null;
            } catch (Exception ex) {
                _logger.LogError(ex, "Failed to rollback PG transaction");
            }
        }
        
        if (_connection != null) {
            try {
                _connection.Close();
                _connection = null;
            } catch (Exception ex) {
                _logger.LogError(ex, "Failed to release PG connection");
            }
        }
    }

    private List<SerializedEvent.SerializedEvent> FindAllSerializedEventsByAggregateId(string aggregateId)
    {
        var events = new List<SerializedEvent.SerializedEvent>();
        var sql = $"""
            SELECT id, event_id, aggregate_id, causation_id, correlation_id, 
                   aggregate_version, json_payload, json_metadata, recorded_on, event_name
            FROM {_eventStoreTable}
            WHERE aggregate_id = @aggregateId 
            ORDER BY aggregate_version ASC
            """;

        using var command = _connection.CreateCommand();
        command.CommandText = sql;
        command.Parameters.AddWithValue("@aggregateId", aggregateId);
        command.Transaction = _activeTransaction;

        try {
            using var reader = command.ExecuteReader();
            while (reader.Read()) {
                events.Add(MapResultSetToSerializedEvent(reader));
            }
            return events;
        } catch (Exception ex) {
            throw new Exception($"Failed to fetch events for aggregate: {aggregateId}", ex);
        }
    }

    private void SaveSerializedEvent(SerializedEvent.SerializedEvent serializedEvent)
    {
        var sql = $"""
            INSERT INTO {_eventStoreTable} (
                event_id, aggregate_id, causation_id, correlation_id, 
                aggregate_version, json_payload, json_metadata, recorded_on, event_name
            ) VALUES (@eventId, @aggregateId, @causationId, @correlationId, 
                     @aggregateVersion, @jsonPayload, @jsonMetadata, @recordedOn, @eventName)
            """;

        using var command = _connection.CreateCommand();
        command.CommandText = sql;
        command.Parameters.AddWithValue("@eventId", serializedEvent.EventId);
        command.Parameters.AddWithValue("@aggregateId", serializedEvent.AggregateId);
        command.Parameters.AddWithValue("@causationId", serializedEvent.CausationId);
        command.Parameters.AddWithValue("@correlationId", serializedEvent.CorrelationId);
        command.Parameters.AddWithValue("@aggregateVersion", serializedEvent.AggregateVersion);
        command.Parameters.AddWithValue("@jsonPayload", serializedEvent.JsonPayload);
        command.Parameters.AddWithValue("@jsonMetadata", serializedEvent.JsonMetadata);
        command.Parameters.AddWithValue("@recordedOn", serializedEvent.RecordedOn);
        command.Parameters.AddWithValue("@eventName", serializedEvent.EventName);
        command.Transaction = _activeTransaction;

        try {
            command.ExecuteNonQuery();
        } catch (Exception ex) {
            throw new Exception($"Failed to save event: {serializedEvent.EventId}", ex);
        }
    }

    private SerializedEvent.SerializedEvent? FindSerializedEventByEventId(string eventId)
    {
        var sql = $"""
            SELECT id, event_id, aggregate_id, causation_id, correlation_id, 
                   aggregate_version, json_payload, json_metadata, recorded_on, event_name
            FROM {_eventStoreTable}
            WHERE event_id = @eventId
            """;

        using var command = _connection.CreateCommand();
        command.CommandText = sql;
        command.Parameters.AddWithValue("@eventId", eventId);
        command.Transaction = _activeTransaction;

        try {
            using var reader = command.ExecuteReader();
            return reader.Read() ? 
                MapResultSetToSerializedEvent(reader) : 
                null;
        } catch (Exception ex) {
            throw new Exception($"Failed to fetch event: {eventId}", ex);
        }
    }

    private SerializedEvent.SerializedEvent MapResultSetToSerializedEvent(NpgsqlDataReader reader)
    {
        return new SerializedEvent.SerializedEvent()
            {
                Id = reader.GetInt32(reader.GetOrdinal("id")),
                EventId = reader.GetString(reader.GetOrdinal("event_id")),
                AggregateId = reader.GetString(reader.GetOrdinal("aggregate_id")),
                CausationId = reader.GetString(reader.GetOrdinal("causation_id")),
                CorrelationId = reader.GetString(reader.GetOrdinal("correlation_id")),
                AggregateVersion = reader.GetInt32(reader.GetOrdinal("aggregate_version")),
                JsonPayload = reader.GetString(reader.GetOrdinal("json_payload")),
                JsonMetadata = reader.GetString(reader.GetOrdinal("json_metadata")),
                RecordedOn = reader.GetString(reader.GetOrdinal("recorded_on")),
                EventName = reader.GetString(reader.GetOrdinal("event_name"))
            };
    }
}
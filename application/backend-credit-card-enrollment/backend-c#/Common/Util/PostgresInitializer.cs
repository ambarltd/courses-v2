using Npgsql;

namespace CreditCardEnrollment.Common.Util;

public class PostgresInitializer
{
    private readonly PostgresConnectionPool _connectionPool;
    private readonly string _databaseName;
    private readonly string _tableName;
    private readonly string _replicationUsername;
    private readonly string _replicationPassword;
    private readonly string _replicationPublication;
    private readonly ILogger<PostgresInitializer> _logger;

    public PostgresInitializer(
        PostgresConnectionPool connectionPool,
        string databaseName,
        string tableName,
        string replicationUsername,
        string replicationPassword,
        string replicationPublication,
        ILogger<PostgresInitializer> logger)
    {
        _connectionPool = connectionPool;
        _databaseName = databaseName;
        _tableName = tableName;
        _replicationUsername = replicationUsername;
        _replicationPassword = replicationPassword;
        _replicationPublication = replicationPublication;
        _logger = logger;
    }

    public void Initialize()
    {
        using var connection = _connectionPool.OpenConnection();
        try
        {
            // Create table
            _logger.LogInformation("Creating table {TableName}", _tableName);
            ExecuteStatementIgnoreErrors(connection, $"""
                CREATE TABLE IF NOT EXISTS {_tableName} (
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
                    PRIMARY KEY (id));
                """);

            // Create replication user
            _logger.LogInformation("Creating replication user");
            ExecuteStatementIgnoreErrors(connection,
                $"CREATE USER {_replicationUsername} REPLICATION LOGIN PASSWORD '{_replicationPassword}';");

            // Grant permissions to user
            _logger.LogInformation("Granting permissions to replication user");
            ExecuteStatementIgnoreErrors(connection,
                $"""GRANT CONNECT ON DATABASE "{_databaseName}" TO {_replicationUsername};""");

            _logger.LogInformation("Granting select to replication user");
            ExecuteStatementIgnoreErrors(connection,
                $"GRANT SELECT ON TABLE {_tableName} TO {_replicationUsername};");

            // Create publication
            _logger.LogInformation("Creating publication for table");
            ExecuteStatementIgnoreErrors(connection,
                $"CREATE PUBLICATION {_replicationPublication} FOR TABLE {_tableName};");

            // Create indexes
            _logger.LogInformation("Creating aggregate id, aggregate version index");
            ExecuteStatementIgnoreErrors(connection,
                $"CREATE UNIQUE INDEX event_store_idx_event_aggregate_id_version ON {_tableName}(aggregate_id, aggregate_version);");

            _logger.LogInformation("Creating id index");
            ExecuteStatementIgnoreErrors(connection,
                $"CREATE UNIQUE INDEX event_store_idx_event_id ON {_tableName}(event_id);");

            _logger.LogInformation("Creating causation index");
            ExecuteStatementIgnoreErrors(connection,
                $"CREATE INDEX event_store_idx_event_causation_id ON {_tableName}(causation_id);");

            _logger.LogInformation("Creating correlation index");
            ExecuteStatementIgnoreErrors(connection,
                $"CREATE INDEX event_store_idx_event_correlation_id ON {_tableName}(correlation_id);");

            _logger.LogInformation("Creating recording index");
            ExecuteStatementIgnoreErrors(connection,
                $"CREATE INDEX event_store_idx_occurred_on ON {_tableName}(recorded_on);");

            _logger.LogInformation("Creating event name index");
            ExecuteStatementIgnoreErrors(connection,
                $"CREATE INDEX event_store_idx_event_name ON {_tableName}(event_name);");
        }
        finally
        {
            connection.Close();
        }
    }

    private void ExecuteStatementIgnoreErrors(NpgsqlConnection connection, string sqlStatement)
    {
        try
        {
            _logger.LogInformation("Executing SQL: {SqlStatement}", sqlStatement);
            using var cmd = new NpgsqlCommand(sqlStatement, connection);
            cmd.ExecuteNonQuery();
        }
        catch (Exception e)
        {
            _logger.LogWarning(e, "Caught exception when executing SQL statement");
        }
    }
}
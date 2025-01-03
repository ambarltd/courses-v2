using Npgsql;

namespace CreditCardEnrollment.Common.Util;

public class PostgresConnectionPool
{
    private readonly NpgsqlDataSource _dataSource;

    public PostgresConnectionPool(string connectionString)
    {
        var builder = new NpgsqlConnectionStringBuilder(connectionString)
        {
            MaxPoolSize = 10,
            MinPoolSize = 5,
            ConnectionIdleLifetime = 300,
            Timeout = 20,
            Enlist = false
        };

        _dataSource = new NpgsqlDataSourceBuilder(builder.ToString()).Build();
    }

    public NpgsqlConnection OpenConnection()
    {
        return _dataSource.OpenConnection();
    }
}
using CreditCardEnrollment.Application.Services;
using CreditCardEnrollment.Common.EventStore;
using Microsoft.EntityFrameworkCore;
using MongoDB.Driver;
using Microsoft.OpenApi.Models;

var builder = WebApplication.CreateBuilder(args);

// Add services to the container.
builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen(c =>
{
    c.SwaggerDoc("v1", new OpenApiInfo { Title = "Credit Card Enrollment API", Version = "v1" });
});

// Add MediatR
builder.Services.AddMediatR(cfg => 
    cfg.RegisterServicesFromAssembly(typeof(Program).Assembly));

// Configure PostgreSQL from environment variables
builder.Services.AddDbContext<EventStoreDbContext>(options =>
{
    var host = Environment.GetEnvironmentVariable("EVENT_STORE_HOST") ?? "localhost";
    var port = Environment.GetEnvironmentVariable("EVENT_STORE_PORT") ?? "5432";
    var database = Environment.GetEnvironmentVariable("EVENT_STORE_DATABASE_NAME") ?? "credit_card_enrollment_events";
    var username = Environment.GetEnvironmentVariable("EVENT_STORE_USER") ?? "postgres";
    var password = Environment.GetEnvironmentVariable("EVENT_STORE_PASSWORD") ?? "postgres";

    var connectionString = $"Host={host};Port={port};Database={database};Username={username};Password={password}";
    options.UseNpgsql(connectionString);
});

// Configure MongoDB from environment variables
builder.Services.AddSingleton<IMongoClient>(sp =>
{
    var host = Environment.GetEnvironmentVariable("MONGODB_PROJECTION_HOST") ?? "localhost";
    var port = Environment.GetEnvironmentVariable("MONGODB_PROJECTION_PORT") ?? "27017";
    var username = Environment.GetEnvironmentVariable("MONGODB_PROJECTION_DATABASE_USERNAME") ?? "mongodb";
    var password = Environment.GetEnvironmentVariable("MONGODB_PROJECTION_DATABASE_PASSWORD") ?? "mongodb";
    var authDb = Environment.GetEnvironmentVariable("MONGODB_PROJECTION_AUTHENTICATION_DATABASE") ?? "admin";

    var connectionString = $"mongodb://{username}:{password}@{host}:{port}/?authSource={authDb}";
    var settings = MongoClientSettings.FromConnectionString(connectionString);
    settings.ReplicaSetName = "rs0"; // This is fixed in the docker-compose setup
    return new MongoClient(settings);
});

builder.Services.AddScoped<IMongoDatabase>(sp =>
{
    var client = sp.GetRequiredService<IMongoClient>();
    var databaseName = Environment.GetEnvironmentVariable("MONGODB_PROJECTION_DATABASE_NAME") ?? "projections";
    return client.GetDatabase(databaseName);
});

// Register services
builder.Services.AddScoped<PostgresEventStore>();
builder.Services.AddScoped<ISessionService, SessionService>();
builder.Services.AddScoped<IProductService, ProductService>();

var app = builder.Build();

// Configure the HTTP request pipeline.
if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI(c =>
    {
        c.SwaggerEndpoint("/swagger/v1/swagger.json", "Credit Card Enrollment API V1");
    });
}

app.UseHttpsRedirection();
app.UseAuthorization();
app.MapControllers();

// Ensure databases are created
using (var scope = app.Services.CreateScope())
{
    var eventStoreContext = scope.ServiceProvider.GetRequiredService<EventStoreDbContext>();
    await eventStoreContext.Database.EnsureCreatedAsync();

    // Get replication settings from environment variables
    var tableName = Environment.GetEnvironmentVariable("EVENT_STORE_CREATE_TABLE_WITH_NAME") ?? "event_store";
    var replicationUser = Environment.GetEnvironmentVariable("EVENT_STORE_CREATE_REPLICATION_USER_WITH_USERNAME");
    var replicationPassword = Environment.GetEnvironmentVariable("EVENT_STORE_CREATE_REPLICATION_USER_WITH_PASSWORD");
    var publicationName = Environment.GetEnvironmentVariable("EVENT_STORE_CREATE_REPLICATION_PUBLICATION");

    // Set up replication if all required variables are present
    if (!string.IsNullOrEmpty(publicationName) && 
        !string.IsNullOrEmpty(replicationUser) && 
        !string.IsNullOrEmpty(replicationPassword))
    {
        // Create replication user and publication. Note this will throw a warn during compilation.
        await eventStoreContext.Database.ExecuteSqlRawAsync($@"
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT FROM pg_user WHERE usename = '{replicationUser}') THEN
                    CREATE USER {replicationUser} WITH REPLICATION PASSWORD '{replicationPassword}';
                END IF;
            END $$;

            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_publication WHERE pubname = '{publicationName}') THEN
                    CREATE PUBLICATION {publicationName} FOR TABLE {tableName};
                END IF;
            END $$;
        ");
    }
}

app.Run();

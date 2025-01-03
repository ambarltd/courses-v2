using CreditCardEnrollment.Common.Command;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Common.Query;
using CreditCardEnrollment.Common.Reaction;
using CreditCardEnrollment.Common.SerializedEvent;
using CreditCardEnrollment.Common.Util;
using Microsoft.Extensions.Logging.Console;

var builder = WebApplication.CreateBuilder(args);

var postgresConnectionString = 
    $"Host={GetEnvVar("EVENT_STORE_HOST")};" +
    $"Port={GetEnvVar("EVENT_STORE_PORT")};" +
    $"Database={GetEnvVar("EVENT_STORE_DATABASE_NAME")};" +
    $"Username={GetEnvVar("EVENT_STORE_USER")};" +
    $"Password={GetEnvVar("EVENT_STORE_PASSWORD")};";
var postgresTableName = GetEnvVar("EVENT_STORE_CREATE_TABLE_WITH_NAME");
builder.Services.AddSingleton(_ => new PostgresConnectionPool(postgresConnectionString));
builder.Services.AddSingleton(_ => new Deserializer());
builder.Services.AddSingleton(_ => new Serializer());
builder.Services.AddScoped<PostgresTransactionalEventStore>(provider => {
    var pool = provider.GetRequiredService<PostgresConnectionPool>();
    var deserializer = provider.GetRequiredService<Deserializer>();
    var serializer = provider.GetRequiredService<Serializer>();
    var eventStoreTable = postgresTableName; 

    return new PostgresTransactionalEventStore(pool, serializer, deserializer, eventStoreTable);
});

var mongoConnectionString = 
    $"mongodb://{GetEnvVar("MONGODB_PROJECTION_DATABASE_USERNAME")}:{GetEnvVar("MONGODB_PROJECTION_DATABASE_PASSWORD")}@" +
    $"{GetEnvVar("MONGODB_PROJECTION_HOST")}:{GetEnvVar("MONGODB_PROJECTION_PORT")}/" +
    $"{GetEnvVar("MONGODB_PROJECTION_DATABASE_NAME")}" +
    "?serverSelectionTimeoutMS=10000&connectTimeoutMS=10000&authSource=admin";
var mongoDatabaseName = GetEnvVar("MONGODB_PROJECTION_DATABASE_NAME");
builder.Services.AddSingleton(_ => new MongoSessionPool(mongoConnectionString));
builder.Services.AddScoped<MongoTransactionalProjectionOperator>(provider =>
{
    var sessionPool = provider.GetRequiredService<MongoSessionPool>();
    return new MongoTransactionalProjectionOperator(sessionPool, mongoDatabaseName);
});

AddScopedInheritors<CommandController>(builder.Services);
AddScopedInheritors<CommandHandler>(builder.Services);
AddScopedInheritors<ProjectionController>(builder.Services);
AddScopedInheritors<ProjectionHandler>(builder.Services);
AddScopedInheritors<QueryController>(builder.Services);
AddScopedInheritors<QueryHandler>(builder.Services);
AddScopedInheritors<ReactionController>(builder.Services);
AddScopedInheritors<ReactionHandler>(builder.Services);

builder.Services.Scan(scan => scan
    .FromAssemblies(AppDomain.CurrentDomain.GetAssemblies())
    .AddClasses(classes => classes.Where(type => 
        type.Namespace != null && type.Namespace.StartsWith("CreditCardEnrollment.CreditCard")))
    .AsSelfWithInterfaces()
    .WithScopedLifetime());

builder.Services.AddControllers();

builder.Services.AddLogging(logging =>
{
    logging.ClearProviders();
    logging.AddConsole(options =>
    {
        options.FormatterName = "MainLogger";
        options.LogToStandardErrorThreshold = LogLevel.Error;
    }).AddConsoleFormatter<Logger, ConsoleFormatterOptions>();

    logging.AddFilter("CreditCardEnrollment", LogLevel.Debug);
    
    logging.SetMinimumLevel(LogLevel.Debug);
});

var app = builder.Build();
app.MapControllers();
app.Run();
return;

static string GetEnvVar(string name) => 
    Environment.GetEnvironmentVariable(name) ?? throw new ArgumentNullException(name);

static void AddScopedInheritors<T>(IServiceCollection services) {
    services.Scan(scan => scan
        .FromAssemblyOf<T>()
        .AddClasses(classes => classes
            .AssignableTo<T>())
        .AsSelfWithInterfaces()
        .WithScopedLifetime());
}

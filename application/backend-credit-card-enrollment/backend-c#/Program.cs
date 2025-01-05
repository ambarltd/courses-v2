using CreditCardEnrollment.Common.Command;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Common.Query;
using CreditCardEnrollment.Common.Reaction;
using CreditCardEnrollment.Common.SerializedEvent;
using CreditCardEnrollment.Common.SessionAuth;
using CreditCardEnrollment.Common.Util;
using Microsoft.AspNetCore.Diagnostics;
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
    var logger = provider.GetRequiredService<ILogger<PostgresTransactionalEventStore>>();

    return new PostgresTransactionalEventStore(pool, serializer, deserializer, eventStoreTable, logger);
});
builder.Services.AddSingleton<PostgresInitializer>(provider => {
    var pool = provider.GetRequiredService<PostgresConnectionPool>();
    var logger = provider.GetRequiredService<ILogger<PostgresInitializer>>();
    return new PostgresInitializer(
        pool,
        GetEnvVar("EVENT_STORE_DATABASE_NAME"),
        GetEnvVar("EVENT_STORE_CREATE_TABLE_WITH_NAME"),
        GetEnvVar("EVENT_STORE_CREATE_REPLICATION_USER_WITH_USERNAME"),
        GetEnvVar("EVENT_STORE_CREATE_REPLICATION_USER_WITH_PASSWORD"),
        GetEnvVar("EVENT_STORE_CREATE_REPLICATION_PUBLICATION"),
        logger
    );
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
    var logger = provider.GetRequiredService<ILogger<MongoTransactionalProjectionOperator>>();
    return new MongoTransactionalProjectionOperator(sessionPool, mongoDatabaseName, logger);
});
builder.Services.AddSingleton<MongoInitializer>(provider => {
    var pool = provider.GetRequiredService<MongoSessionPool>();
    var logger = provider.GetRequiredService<ILogger<MongoInitializer>>();
    return new MongoInitializer(
        pool,
        GetEnvVar("MONGODB_PROJECTION_DATABASE_NAME"),
        logger
    );
});

AddScopedInheritors<CommandController>(builder.Services);
AddScopedInheritors<CommandHandler>(builder.Services);
AddScopedInheritors<ProjectionController>(builder.Services);
AddScopedInheritors<ProjectionHandler>(builder.Services);
AddScopedInheritors<QueryController>(builder.Services);
AddScopedInheritors<QueryHandler>(builder.Services);
AddScopedInheritors<ReactionController>(builder.Services);
AddScopedInheritors<ReactionHandler>(builder.Services);
builder.Services.AddScoped<SessionRepository>();
builder.Services.AddScoped<SessionService>(provider =>
{
    var sessionRepository = provider.GetRequiredService<SessionRepository>();
    var sessionExpirationSeconds = int.Parse(GetEnvVar("SESSION_TOKENS_EXPIRE_AFTER_SECONDS"));

    return new SessionService(sessionRepository, sessionExpirationSeconds);
});

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

    logging.SetMinimumLevel(LogLevel.Debug);
    
    logging.AddFilter("CreditCardEnrollment", LogLevel.Debug);
    logging.AddFilter("Microsoft", LogLevel.Information);
});

var app = builder.Build();

// Initialize databases
var postgresInitializer = app.Services.GetRequiredService<PostgresInitializer>();
var mongoInitializer = app.Services.GetRequiredService<MongoInitializer>();
postgresInitializer.Initialize();
mongoInitializer.Initialize();

// Register app exception handler
app.UseExceptionHandler(errorApp =>
{
    errorApp.Run(async context =>
    {
        var exceptionHandlerPathFeature = context.Features.Get<IExceptionHandlerPathFeature>();
        var exception = exceptionHandlerPathFeature?.Error;
        
        var logger = context.RequestServices.GetRequiredService<ILogger<Program>>();
        logger.LogError(exception, "Unhandled exception: {Message}. Stack Trace: {StackTrace}", exception?.Message, exception?.StackTrace);

        context.Response.StatusCode = StatusCodes.Status500InternalServerError;
        context.Response.ContentType = "application/json";
        
        await context.Response.WriteAsJsonAsync(new {
            error = exception?.Message,
            stackTrace = exception?.StackTrace
        });
    });
});

// Run app
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

using CreditCardEnrollment.Common.Command;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Common.Query;
using CreditCardEnrollment.Common.Reaction;
using CreditCardEnrollment.Common.SerializedEvent;
using CreditCardEnrollment.Common.Util;

var builder = WebApplication.CreateBuilder(args);

var postgresConnectionString = 
    $"jdbc:postgresql://{GetEnvVar("EVENT_STORE_HOST")}:{GetEnvVar("EVENT_STORE_PORT")}/" +
    $"{GetEnvVar("EVENT_STORE_DATABASE_NAME")}?user={GetEnvVar("EVENT_STORE_USER")}&password={GetEnvVar("EVENT_STORE_PASSWORD")}";
var mongoConnectionString = 
    $"mongodb://{GetEnvVar("MONGODB_PROJECTION_DATABASE_USERNAME")}:{GetEnvVar("MONGODB_PROJECTION_DATABASE_PASSWORD")}@" +
    $"{GetEnvVar("MONGODB_PROJECTION_HOST")}:{GetEnvVar("MONGODB_PROJECTION_PORT")}/" +
    $"{GetEnvVar("MONGODB_PROJECTION_DATABASE_NAME")}" +
    "?serverSelectionTimeoutMS=10000&connectTimeoutMS=10000&authSource=admin";
var mongoDatabaseName = GetEnvVar("MONGODB_PROJECTION_DATABASE_NAME");

builder.Services.AddSingleton(_ => new PostgresConnectionPool(postgresConnectionString));
builder.Services.AddSingleton(_ => new MongoSessionPool(mongoConnectionString, mongoDatabaseName));
builder.Services.AddSingleton(_ => new Deserializer());
builder.Services.AddSingleton(_ => new Serializer());
builder.Services.AddScoped<PostgresTransactionalEventStore>();
builder.Services.AddScoped<MongoTransactionalProjectionOperator>();

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
    .AsImplementedInterfaces()
    .WithScopedLifetime());

var app = builder.Build();
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

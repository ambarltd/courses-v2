using System.Text.Json;
using CreditCardEnrollment.Common.Events;
using Microsoft.EntityFrameworkCore;

namespace CreditCardEnrollment.Common.EventStore;

public class PostgresEventStore(EventStoreDbContext context)
{
    public async Task SaveEventAsync(Event @event)
    {
        var serializedEvent = new SerializedEvent
        {
            EventId = @event.EventId,
            AggregateId = @event.AggregateId,
            AggregateVersion = @event.AggregateVersion,
            EventType = @event.GetType().FullName!,
            EventData = JsonSerializer.Serialize(@event),
            CorrelationId = @event.CorrelationId,
            CausationId = @event.CausationId,
            RecordedOn = @event.RecordedOn
        };

        context.Events.Add(serializedEvent);
        await context.SaveChangesAsync();
    }

    public async Task<IEnumerable<Event>> GetEventsForAggregateAsync(string aggregateId)
    {
        var serializedEvents = await context.Events
            .Where(e => e.AggregateId == aggregateId)
            .OrderBy(e => e.AggregateVersion)
            .ToListAsync();

        return serializedEvents.Select(se => 
        {
            var eventType = Type.GetType(se.EventType);
            if (eventType == null)
                throw new InvalidOperationException($"Event type {se.EventType} not found");
                
            return (Event)JsonSerializer.Deserialize(se.EventData, eventType)!;
        });
    }
}

public class EventStoreDbContext : DbContext
{
    private readonly string _tableName;
    public DbSet<SerializedEvent> Events { get; set; } = null!;

    public EventStoreDbContext(DbContextOptions<EventStoreDbContext> options)
        : base(options)
    {
        _tableName = Environment.GetEnvironmentVariable("EVENT_STORE_CREATE_TABLE_WITH_NAME") ?? "event_store";
    }

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        modelBuilder.Entity<SerializedEvent>(builder =>
        {
            builder.ToTable(_tableName);
            builder.HasKey(e => e.EventId);
            builder.Property(e => e.EventId).HasColumnName("event_id");
            builder.Property(e => e.AggregateId).HasColumnName("aggregate_id");
            builder.Property(e => e.AggregateVersion).HasColumnName("aggregate_version");
            builder.Property(e => e.EventType).HasColumnName("event_type");
            builder.Property(e => e.EventData).HasColumnName("event_data");
            builder.Property(e => e.CorrelationId).HasColumnName("correlation_id");
            builder.Property(e => e.CausationId).HasColumnName("causation_id");
            builder.Property(e => e.RecordedOn).HasColumnName("recorded_on");
        });
    }

    protected override void OnConfiguring(DbContextOptionsBuilder optionsBuilder)
    {
        base.OnConfiguring(optionsBuilder);

        // Enable logical replication
        optionsBuilder.UseNpgsql(options => options.EnableRetryOnFailure());
    }
}

public class SerializedEvent
{
    public string EventId { get; set; } = string.Empty;
    public string AggregateId { get; set; } = string.Empty;
    public int AggregateVersion { get; set; }
    public string EventType { get; set; } = string.Empty;
    public string EventData { get; set; } = string.Empty;
    public string CorrelationId { get; set; } = string.Empty;
    public string CausationId { get; set; } = string.Empty;
    public DateTime RecordedOn { get; set; }
}

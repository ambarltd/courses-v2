using System.Text.Json;
using CreditCardEnrollment.Common.Events;
using Microsoft.EntityFrameworkCore;

namespace CreditCardEnrollment.Common.EventStore;

public class PostgresEventStore
{
    private readonly EventStoreDbContext _context;
    private readonly ISerializer _serializer;
    private readonly IDeserializer _deserializer;

    public PostgresEventStore(
        EventStoreDbContext context,
        ISerializer serializer,
        IDeserializer deserializer)
    {
        _context = context;
        _serializer = serializer;
        _deserializer = deserializer;
    }

    public async Task SaveEventAsync(Event @event)
    {
        var serializedEvent = _serializer.Serialize(@event);
        _context.Events.Add(serializedEvent);
        await _context.SaveChangesAsync();
    }

    public async Task<IEnumerable<Event>> GetEventsForAggregateAsync(string aggregateId)
    {
        var serializedEvents = await _context.Events
            .Where(e => e.AggregateId == aggregateId)
            .OrderBy(e => e.AggregateVersion)
            .ToListAsync();

        return serializedEvents.Select(_deserializer.Deserialize);
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
            builder.HasKey(e => e.Id);
            builder.Property(e => e.Id).HasColumnName("id").ValueGeneratedOnAdd();
            builder.Property(e => e.EventId).HasColumnName("event_id");
            builder.Property(e => e.AggregateId).HasColumnName("aggregate_id");
            builder.Property(e => e.AggregateVersion).HasColumnName("aggregate_version");
            builder.Property(e => e.JsonPayload).HasColumnName("json_payload");
            builder.Property(e => e.JsonMetadata).HasColumnName("json_metadata");
            builder.Property(e => e.CorrelationId).HasColumnName("correlation_id");
            builder.Property(e => e.CausationId).HasColumnName("causation_id");
            builder.Property(e => e.RecordedOn).HasColumnName("recorded_on");
            builder.Property(e => e.EventName).HasColumnName("event_name");

            // Add indexes to match Java implementation
            builder.HasIndex(e => new { e.AggregateId, e.AggregateVersion }, "event_store_idx_event_aggregate_id_version")
                .IsUnique();
            builder.HasIndex(e => e.EventId, "event_store_idx_event_id")
                .IsUnique();
            builder.HasIndex(e => e.CausationId, "event_store_idx_event_causation_id");
            builder.HasIndex(e => e.CorrelationId, "event_store_idx_event_correlation_id");
            builder.HasIndex(e => e.RecordedOn, "event_store_idx_recorded_on");
            builder.HasIndex(e => e.EventName, "event_store_idx_event_name");
        });
    }

    protected override void OnConfiguring(DbContextOptionsBuilder optionsBuilder)
    {
        base.OnConfiguring(optionsBuilder);
        optionsBuilder.UseNpgsql(options => options.EnableRetryOnFailure());
    }
}

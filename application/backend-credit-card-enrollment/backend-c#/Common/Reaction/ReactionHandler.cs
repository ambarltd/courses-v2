using CreditCardEnrollment.Common.EventStore;

namespace CreditCardEnrollment.Common.Reaction;

public abstract class ReactionHandler {
    protected readonly PostgresTransactionalEventStore _postgresTransactionalEventStore;

    protected ReactionHandler(PostgresTransactionalEventStore postgresTransactionalEventStore) {
        _postgresTransactionalEventStore = postgresTransactionalEventStore;
    }

    public abstract void React(Event.Event @event);
}
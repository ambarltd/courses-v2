using CreditCardEnrollment.Common.EventStore;

namespace CreditCardEnrollment.Common.Command;

public abstract class CommandHandler {
    protected readonly PostgresTransactionalEventStore _postgresTransactionalEventStore;

    protected CommandHandler(PostgresTransactionalEventStore postgresTransactionalEventStore) {
        _postgresTransactionalEventStore = postgresTransactionalEventStore;
    }

    public abstract void HandleCommand(Command command);
}
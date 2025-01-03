using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Projection;

namespace CreditCardEnrollment.Common.Command;

public class CommandController {
    private readonly PostgresTransactionalEventStore _postgresTransactionalEventStore;
    private readonly MongoTransactionalProjectionOperator _mongoTransactionalProjectionOperator;

    public CommandController(
        PostgresTransactionalEventStore postgresTransactionalEventStore,
        MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator) {
        _postgresTransactionalEventStore = postgresTransactionalEventStore;
        _mongoTransactionalProjectionOperator = mongoTransactionalProjectionOperator;
    }

    public void ProcessCommand(Command command, CommandHandler commandHandler) {
        try {
            _postgresTransactionalEventStore.BeginTransaction();
            _mongoTransactionalProjectionOperator.StartTransaction();
            commandHandler.HandleCommand(command);
            _postgresTransactionalEventStore.CommitTransaction();
            _mongoTransactionalProjectionOperator.CommitTransaction();

            _postgresTransactionalEventStore.AbortDanglingTransactionsAndReturnConnectionToPool();
            _mongoTransactionalProjectionOperator.AbortDanglingTransactionsAndReturnSessionToPool();
        } catch (Exception ex) {
            _postgresTransactionalEventStore.AbortDanglingTransactionsAndReturnConnectionToPool();
            _mongoTransactionalProjectionOperator.AbortDanglingTransactionsAndReturnSessionToPool();
            throw new Exception($"Failed to process command: {ex.Message}", ex);
        }
    }
}
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Projection;

namespace CreditCardEnrollment.Common.Command;

public class CommandController {
    private readonly PostgresTransactionalEventStore _postgresTransactionalEventStore;
    private readonly MongoTransactionalProjectionOperator _mongoTransactionalProjectionOperator;
    private readonly ILogger<CommandController> _logger;

    public CommandController(
        PostgresTransactionalEventStore postgresTransactionalEventStore,
        MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator,
        ILogger<CommandController> logger
    ) {
        _postgresTransactionalEventStore = postgresTransactionalEventStore;
        _mongoTransactionalProjectionOperator = mongoTransactionalProjectionOperator;
        _logger = logger;
    }

    protected void ProcessCommand(Command command, CommandHandler commandHandler) {
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
            _logger.LogError("Exception in ProcessCommand: {0}, {1}", ex.Message, ex.StackTrace);
            throw new Exception($"Failed to process command: {ex.Message}", ex);
        }
    }
}
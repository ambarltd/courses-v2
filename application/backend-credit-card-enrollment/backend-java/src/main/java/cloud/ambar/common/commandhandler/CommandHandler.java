package cloud.ambar.common.commandhandler;

import cloud.ambar.common.eventstore.PostgresTransactionalEventStore;
import lombok.RequiredArgsConstructor;

@RequiredArgsConstructor
abstract public class CommandHandler {
    final protected PostgresTransactionalEventStore postgresTransactionalEventStore;
    protected abstract void handleCommand(Command command);
}

package cloud.ambar.common.commandhandler;

import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import cloud.ambar.common.eventstore.PostgresTransactionalEventStore;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import java.util.Arrays;
import java.util.stream.Collectors;

@RequiredArgsConstructor
public class CommandController {
    private final PostgresTransactionalEventStore postgresTransactionalEventStore;
    private final MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator;
    private static final Logger log = LogManager.getLogger(CommandController.class);

    public void processCommand(
            final Command command,
            final CommandHandler commandHandler
    ) {
        log.info("Command controller received command: " + command);

        try {
            // We start a PG transaction because command handlers need to append to the event store transactionally.
            // I.e., they need to read aggregates and append to them in an ACID fashion.
            // We start a Mongo transaction because if a command handler needs to read from a projection,
            // it also needs to do so transactionally to not receive dirty reads.
            postgresTransactionalEventStore.beginTransaction();
            mongoTransactionalProjectionOperator.startTransaction();
            commandHandler.handleCommand(command);
            postgresTransactionalEventStore.commitTransaction();
            mongoTransactionalProjectionOperator.commitTransaction();

            postgresTransactionalEventStore.abortDanglingTransactionsAndReturnConnectionToPool();
            mongoTransactionalProjectionOperator.abortDanglingTransactionsAndReturnSessionToPool();
        } catch (Exception e) {
            log.error("Failed to process reaction command.");
            log.error(e);
            log.error(e.getMessage());

            String stackTraceString = Arrays.stream(e.getStackTrace())
                    .map(StackTraceElement::toString)
                    .collect(Collectors.joining("\n"));
            log.error(stackTraceString);

            postgresTransactionalEventStore.abortDanglingTransactionsAndReturnConnectionToPool();
            mongoTransactionalProjectionOperator.abortDanglingTransactionsAndReturnSessionToPool();

            throw new RuntimeException("Failed to process command with exception: " + e);
        }
    }
}

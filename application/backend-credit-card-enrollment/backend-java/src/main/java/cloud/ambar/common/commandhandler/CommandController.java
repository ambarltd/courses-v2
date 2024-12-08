package cloud.ambar.common.commandhandler;

import cloud.ambar.common.eventstore.EventStore;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import java.util.Arrays;
import java.util.stream.Collectors;

@RequiredArgsConstructor
public class CommandController {
    private final EventStore eventStore;
    private static final Logger log = LogManager.getLogger(CommandController.class);

    public void processCommand(final Command command, final CommandHandler commandHandler) {
        try {
            eventStore.beginTransaction();
            commandHandler.handleCommand(command);
            eventStore.commitTransaction();
        } catch (Exception e) {
            log.error("Failed to process reaction command.");
            log.error(e);
            log.error(e.getMessage());

            String stackTraceString = Arrays.stream(e.getStackTrace())
                    .map(StackTraceElement::toString)
                    .collect(Collectors.joining("\n"));
            log.error(stackTraceString);

            if (eventStore.isTransactionActive()) {
                eventStore.abortTransaction();
                eventStore.closeSession();
            }
        }
    }
}

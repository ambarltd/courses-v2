package cloud.ambar.common.commandhandler;

import cloud.ambar.common.eventstore.EventStore;
import lombok.RequiredArgsConstructor;

@RequiredArgsConstructor
abstract public class CommandHandler {
    final protected EventStore eventStore;
    protected abstract void handleCommand(Command command);
}

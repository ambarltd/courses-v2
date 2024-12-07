package cloud.ambar.common.commandhandler;

import cloud.ambar.common.eventstore.EventStore;
import lombok.RequiredArgsConstructor;

@RequiredArgsConstructor
public class CommandHandler {
    final protected EventStore eventStore;
}

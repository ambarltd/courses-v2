import { Command } from './Command';
import { PostgresTransactionalEventStore } from '../eventStore/PostgresTransactionalEventStore';

export abstract class CommandHandler {
    constructor(
        protected readonly postgresTransactionalEventStore: PostgresTransactionalEventStore
    ) {}

    abstract handleCommand(command: Command): Promise<void>;
}
import { PostgresTransactionalEventStore } from '../eventStore/PostgresTransactionalEventStore';
import { MongoTransactionalProjectionOperator } from '../projection/MongoTransactionalProjectionOperator';
import { log } from '../util/Logger';
import { Command } from './Command';
import { CommandHandler } from './CommandHandler';

export class CommandController {
    constructor(
        private readonly postgresTransactionalEventStore: PostgresTransactionalEventStore,
        private readonly mongoTransactionalProjectionOperator: MongoTransactionalProjectionOperator,
    ) {}

    protected async processCommand(command: Command, commandHandler: CommandHandler): Promise<void> {
        try {
            log.debug(`Starting to process command: ${command.constructor.name}`);
            await this.postgresTransactionalEventStore.beginTransaction();
            await this.mongoTransactionalProjectionOperator.startTransaction();
            await commandHandler.handleCommand(command);
            await this.postgresTransactionalEventStore.commitTransaction();
            await this.mongoTransactionalProjectionOperator.commitTransaction();

            await this.postgresTransactionalEventStore.abortDanglingTransactionsAndReturnConnectionToPool();
            await this.mongoTransactionalProjectionOperator.abortDanglingTransactionsAndReturnSessionToPool();
            log.debug(`Successfully processed command: ${command.constructor.name}`);
        } catch (error) {
            await this.postgresTransactionalEventStore.abortDanglingTransactionsAndReturnConnectionToPool();
            await this.mongoTransactionalProjectionOperator.abortDanglingTransactionsAndReturnSessionToPool();
            log.error(`Exception in ProcessCommand: ${error}`, error as Error);
            throw new Error(`Failed to process query: ${error}`);
        }
    }
}
import { Event } from '../event/Event';
import { PostgresTransactionalEventStore } from '../eventStore/PostgresTransactionalEventStore';

export abstract class ReactionHandler {
    constructor(
        protected readonly postgresTransactionalEventStore: PostgresTransactionalEventStore
    ) {}

    abstract react(event: Event): Promise<void>;
}
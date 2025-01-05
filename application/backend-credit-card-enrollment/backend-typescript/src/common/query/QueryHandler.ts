import { MongoTransactionalProjectionOperator } from '../projection/MongoTransactionalProjectionOperator';
import { Query } from './Query';

export abstract class QueryHandler {
    constructor(
        protected readonly mongoTransactionalProjectionOperator: MongoTransactionalProjectionOperator
    ) {}

    abstract handleQuery(query: Query): Promise<unknown>;
}
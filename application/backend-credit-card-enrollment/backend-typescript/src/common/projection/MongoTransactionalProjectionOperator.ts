import {
    ClientSession,
    MongoClient,
    Filter,
    FindOptions,
    Document,
    ReplaceOptions,
    InsertOneOptions,
    CountOptions,
    Db,
    ReadConcern,
    WriteConcern,
    ReadPreference,
    TransactionOptions, OptionalUnlessRequiredId, WithId
} from 'mongodb';
import { MongoSessionPool } from '../util/MongoSessionPool';
import { log } from '../util/Logger';
import {inject, injectable} from "tsyringe";

@injectable()
export class MongoTransactionalProjectionOperator {
    private session: ClientSession | null = null;
    private db: Db | null = null;

    constructor(
        @inject(MongoSessionPool) private readonly sessionPool: MongoSessionPool,
        @inject("mongoDatabaseName") private readonly databaseName: string
    ) {}

    async startTransaction(): Promise<void> {
        if (this.session) {
            throw new Error('Session to MongoDB already active!');
        }

        if (this.db) {
            throw new Error('Database already initialized in the current session.');
        }

        try {
            this.session = await this.sessionPool.startSession();

            const client = this.sessionPool.getClient();
            this.db = client.db(this.databaseName);

            const transactionOptions: TransactionOptions = {
                readConcern: new ReadConcern('snapshot'),
                writeConcern: new WriteConcern('majority'),
                readPreference: ReadPreference.primary
            };

            this.session.startTransaction(transactionOptions);
        } catch (error) {
            throw new Error(`Failed to start MongoDB transaction: ${error}`);
        }
    }

    async commitTransaction(): Promise<void> {
        if (!this.session) {
            throw new Error('Session must be active to commit transaction to MongoDB!');
        }

        if (!this.session.inTransaction()) {
            throw new Error('Transaction must be active to commit transaction to MongoDB!');
        }

        try {
            await this.session.commitTransaction();
        } catch (error) {
            throw new Error(`Failed to commit MongoDB transaction: ${error}`);
        }
    }

    async abortDanglingTransactionsAndReturnSessionToPool(): Promise<void> {
        if (!this.session) {
            this.db = null;
            return;
        }

        try {
            if (this.session.inTransaction()) {
                await this.session.abortTransaction();
            }
        } catch (error) {
            log.error('Failed to abort Mongo transaction', error as Error);
        }

        try {
            await this.session.endSession();
        } catch (error) {
            log.error('Failed to release Mongo session', error as Error);
        }

        this.session = null;
        this.db = null;
    }

    async find<T extends Document>(
        collectionName: string,
        filter: Filter<T>,
        options?: FindOptions
    ): Promise<WithId<T>[]> {
        const { session, db } = await this.operate();
        const collection = db.collection<T>(collectionName);
        return collection.find(filter, { ...options, session }).toArray();
    }

    async replaceOne<T extends Document>(
        collectionName: string,
        filter: Filter<T>,
        replacement: T,
        options?: ReplaceOptions
    ): Promise<Document> {
        const { session, db } = await this.operate();
        const collection = db.collection<T>(collectionName);
        return collection.replaceOne(filter, replacement, { ...options, session });
    }

    async insertOne<T extends Document>(
        collectionName: string,
        document: T & OptionalUnlessRequiredId<T>,
        options?: InsertOneOptions
    ): Promise<void> {
        const { session, db } = await this.operate();
        const collection = db.collection<T>(collectionName);
        await collection.insertOne(document, { ...options, session });
    }

    async countDocuments<T extends Document>(
        collectionName: string,
        filter: Filter<T>,
        options?: CountOptions
    ): Promise<number> {
        const { session, db } = await this.operate();
        const collection = db.collection<T>(collectionName);
        return collection.countDocuments(filter, { ...options, session });
    }

    private async operate() {
        if (!this.session) {
            throw new Error('Session must be active to read or write to MongoDB!');
        }

        if (!this.session.inTransaction()) {
            throw new Error('Transaction must be active to read or write to MongoDB!');
        }

        if (!this.db) {
            throw new Error('Database must be initialized in the current session.');
        }

        return { session: this.session, db: this.db };
    }
}
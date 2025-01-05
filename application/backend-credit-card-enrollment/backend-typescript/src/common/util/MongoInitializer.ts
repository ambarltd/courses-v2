import { injectable, inject } from 'tsyringe';
import { MongoClient } from 'mongodb';
import { MongoSessionPool } from './MongoSessionPool';
import { log } from './Logger';

@injectable()
export class MongoInitializer {
    private readonly client: MongoClient;

    constructor(
        @inject(MongoSessionPool) private readonly sessionPool: MongoSessionPool,
        @inject("mongoDatabaseName") private readonly databaseName: string
    ) {
        this.client = this.sessionPool.getClient();
    }

    async initialize(): Promise<void> {
        log.info('Initializing MongoDB collections and indexes...');

        try {
            await this.client.connect();
            const db = this.client.db(this.databaseName);

            // Create collections
            log.info('Creating collections...');
            await Promise.all([
                this.ensureCollection(db, 'CreditCard_Enrollment_Enrollment'),
                this.ensureCollection(db, 'CreditCard_Enrollment_ProductName'),
                this.ensureCollection(db, 'CreditCard_Enrollment_ProductActiveStatus')
            ]);
            log.info('Collections created successfully');

            // Create indexes
            log.info('Creating indexes...');
            await this.createIndexes(db);
            log.info('Indexes created successfully');

        } catch (error) {
            log.error('Error initializing MongoDB:', error as Error);
            throw error;
        }
    }

    private async ensureCollection(db: any, collectionName: string): Promise<void> {
        try {
            const collections = await db.listCollections({ name: collectionName }).toArray();
            if (collections.length === 0) {
                await db.createCollection(collectionName);
                log.debug(`Collection ${collectionName} created`);
            } else {
                log.debug(`Collection ${collectionName} already exists`);
            }
        } catch (error) {
            log.error(`Error ensuring collection ${collectionName}:`, error as Error);
            throw error;
        }
    }

    private async createIndexes(db: any): Promise<void> {
        try {
            const enrollmentCollection = db.collection('CreditCard_Enrollment_Enrollment');

            await enrollmentCollection.createIndex(
                { userId: 1 },
                {
                    background: true,
                    name: 'userId_asc'
                }
            );
            log.debug('Index created on CreditCard_Enrollment_Enrollment.userId');
        } catch (error) {
            log.error('Error creating indexes:', error as Error);
            throw error;
        }
    }
}
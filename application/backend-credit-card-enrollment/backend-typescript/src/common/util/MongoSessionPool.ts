import { MongoClient, ClientSession, ServerApiVersion } from 'mongodb';

export class MongoSessionPool {
    private readonly transactionalClient: MongoClient;

    constructor(connectionString: string) {
        const settings = {
            maxPoolSize: 20,
            minPoolSize: 5,
            maxIdleTimeMS: 10 * 60 * 1000, // 10 minutes
            maxConnecting: 30,
            waitQueueTimeoutMS: 2000,
            replicaSet: 'rs0',
            serverApi: {
                version: ServerApiVersion.v1,
                strict: true,
                deprecationErrors: true
            }
        };

        this.transactionalClient = new MongoClient(connectionString, settings);
    }

    async startSession(): Promise<ClientSession> {
        await this.transactionalClient.connect();
        return this.transactionalClient.startSession();
    }

    async close(): Promise<void> {
        await this.transactionalClient.close();
    }
}
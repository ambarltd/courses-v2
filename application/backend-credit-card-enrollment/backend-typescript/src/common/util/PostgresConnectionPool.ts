import { Pool, PoolConfig, PoolClient } from 'pg';
import {inject, injectable} from "tsyringe";

@injectable()
export class PostgresConnectionPool {
    private readonly pool: Pool;

    constructor(@inject("postgresConnectionString") connectionString: string) {
        const config: PoolConfig = {
            connectionString,
            max: 10,
            min: 5,
            idleTimeoutMillis: 300000, // 5 minutes
            connectionTimeoutMillis: 20000, // 20 seconds
        };

        this.pool = new Pool(config);

        this.pool.on('error', (err) => {
            console.error('Unexpected error on idle client', err);
        });
    }

    async openConnection(): Promise<PoolClient> {
        try {
            return await this.pool.connect();
        } catch (error) {
            throw new Error(`Failed to open database connection: ${error}`);
        }
    }

    async close(): Promise<void> {
        await this.pool.end();
    }
}
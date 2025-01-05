import { Document, Filter } from 'mongodb';
import {MongoTransactionalProjectionOperator} from "../projection/MongoTransactionalProjectionOperator";
import {inject, injectable} from "tsyringe";

@injectable()
export class SessionRepository {
    private readonly mongoOperator: MongoTransactionalProjectionOperator;

    constructor(
        @inject(MongoTransactionalProjectionOperator) mongoOperator: MongoTransactionalProjectionOperator
    ) {
        this.mongoOperator = mongoOperator;
    }

    async authenticatedUserIdFromSessionToken(
        sessionToken: string,
        sessionExpirationSeconds: number
    ): Promise<string | null> {
        const sessionCollectionName = 'AuthenticationForAllContexts_Session_Session';

        try {
            const sessions = await this.mongoOperator.find<Document>(
                sessionCollectionName,
                { sessionToken } as Filter<Document>
            );

            if (sessions.length === 0) return null;

            const session = sessions[0];
            if (session.signedOut) return null;

            const tokenLastRefreshedStr = session.tokenLastRefreshedAt as string;
            const tokenLastRefreshed = new Date(tokenLastRefreshedStr).getTime();

            if (
                tokenLastRefreshed <
                Date.now() - sessionExpirationSeconds * 1000 // Convert seconds to milliseconds
            ) {
                return null;
            }

            return session.userId as string;
        } catch (error) {
            throw new Error(
                `Error fetching user ID from session token: ${(error as Error).message}`
            );
        }
    }
}

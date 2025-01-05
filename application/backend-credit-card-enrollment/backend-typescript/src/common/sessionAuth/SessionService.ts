import { SessionRepository } from './SessionRepository';
import {inject, injectable} from "tsyringe";

@injectable()
export class SessionService {
    private readonly sessionRepository: SessionRepository;
    private readonly sessionExpirationSeconds: number;

    constructor(
        @inject(SessionRepository) sessionRepository: SessionRepository,
        @inject("sessionExpirationSeconds") sessionExpirationSeconds: number
    ) {
        this.sessionRepository = sessionRepository;
        this.sessionExpirationSeconds = sessionExpirationSeconds;
    }

    async authenticatedUserIdFromSessionToken(sessionToken: string): Promise<string> {
        const userId = await this.sessionRepository.authenticatedUserIdFromSessionToken(
            sessionToken,
            this.sessionExpirationSeconds
        );

        if (!userId) {
            throw new Error('Invalid session');
        }

        return userId;
    }
}

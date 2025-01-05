import { SessionRepository } from './SessionRepository';

export class SessionService {
    private readonly sessionRepository: SessionRepository;
    private readonly sessionExpirationSeconds: number;

    constructor(sessionRepository: SessionRepository, sessionExpirationSeconds: number) {
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

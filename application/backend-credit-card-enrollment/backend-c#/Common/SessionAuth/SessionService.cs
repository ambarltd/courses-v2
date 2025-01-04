namespace CreditCardEnrollment.Common.SessionAuth;

public class SessionService {
    private readonly SessionRepository _sessionRepository;
    private readonly int _sessionExpirationSeconds;

    public SessionService(SessionRepository sessionRepository, int sessionExpirationSeconds) {
        _sessionRepository = sessionRepository;
        _sessionExpirationSeconds = sessionExpirationSeconds;
    }

    public string AuthenticatedUserIdFromSessionToken(string sessionToken) {
        return _sessionRepository.AuthenticatedUserIdFromSessionToken(sessionToken, _sessionExpirationSeconds) 
               ?? throw new Exception("Invalid session");
    }
}
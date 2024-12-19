namespace CreditCardEnrollment.Common.Services;

public interface ISessionService
{
    string GetAuthenticatedUserId(string sessionToken);
}

public class SessionService : ISessionService
{
    public string GetAuthenticatedUserId(string sessionToken)
    {
        // In a real implementation, this would validate the session token
        // and return the associated user ID
        if (string.IsNullOrEmpty(sessionToken))
            throw new UnauthorizedAccessException("Invalid session token");
            
        return sessionToken; // Simplified for demo
    }
}

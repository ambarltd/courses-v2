using MongoDB.Bson;
using MongoDB.Driver;
using CreditCardEnrollment.Common.Projection;

namespace CreditCardEnrollment.Common.SessionAuth;

public class SessionRepository {
    private readonly MongoTransactionalProjectionOperator _mongoOperator;

    public SessionRepository(MongoTransactionalProjectionOperator mongoOperator) {
        _mongoOperator = mongoOperator;
    }

    public string? AuthenticatedUserIdFromSessionToken(string sessionToken, int sessionExpirationSeconds) {
        var filter = Builders<BsonDocument>.Filter.Eq("sessionToken", sessionToken);
        var session = _mongoOperator.Operate()
            .GetCollection<BsonDocument>("AuthenticationForAllContexts_Session_Session")
            .Find(filter)
            .FirstOrDefault();

        if (session == null) return null;
        
        if (session.GetValue("signedOut").AsBoolean) return null;

        var tokenLastRefreshed = session.GetValue("tokenLastRefreshedAt").ToUniversalTime();
        if (tokenLastRefreshed < DateTime.UtcNow.AddSeconds(-sessionExpirationSeconds)) return null;

        return session.GetValue("userId").AsString;
    }
}
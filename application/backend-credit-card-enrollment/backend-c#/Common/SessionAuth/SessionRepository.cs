using MongoDB.Bson;
using MongoDB.Driver;
using CreditCardEnrollment.Common.Projection;
using System;

namespace CreditCardEnrollment.Common.SessionAuth;

public class SessionRepository {
    private readonly MongoTransactionalProjectionOperator _mongoOperator;

    public SessionRepository(MongoTransactionalProjectionOperator mongoOperator) {
        _mongoOperator = mongoOperator;
    }

    public string? AuthenticatedUserIdFromSessionToken(string sessionToken, int sessionExpirationSeconds) {
        var session = _mongoOperator
            .Find<BsonDocument>(
                "AuthenticationForAllContexts_Session_Session", 
                doc => doc["sessionToken"] == sessionToken
            ).FirstOrDefault();

        if (session == null) return null;
        
        if (session.GetValue("signedOut").AsBoolean) return null;

        var tokenLastRefreshedStr = session.GetValue("tokenLastRefreshedAt").AsString;
        var tokenLastRefreshed = DateTimeOffset.Parse(tokenLastRefreshedStr).UtcDateTime;

        if (tokenLastRefreshed < DateTime.UtcNow.AddSeconds(-sessionExpirationSeconds)) return null;

        return session.GetValue("userId").AsString;
    }
}
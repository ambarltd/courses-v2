package cloud.ambar.common.sessionauth;

import cloud.ambar.common.projection.MongoTransactionalAPI;
import lombok.RequiredArgsConstructor;
import org.springframework.data.mongodb.core.query.Criteria;
import org.springframework.data.mongodb.core.query.Query;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.time.Instant;
import java.util.Optional;

@Service
@RequiredArgsConstructor
@RequestScope
public class SessionRepository {
    private final SessionConfig sessionConfig;
    private final MongoTransactionalAPI mongoTransactionalAPI;
    public Optional<String> authenticatedUserIdFromSessionToken(String sessionToken) {
        Session session = mongoTransactionalAPI.operate().findOne(
                Query.query(
                        Criteria.where("sessionToken").is(sessionToken)
                ),
                Session.class,
                "AuthenticationForAllContexts_Session_Session"
        );

        if (session == null) {
            return Optional.empty();
        }

        if (session.getSignedOut()) {
            return Optional.empty();
        }

        if (session.getTokenLastRefreshedAt().isBefore(Instant.now().minusSeconds(sessionConfig.getSessionTokensExpireAfterSeconds()))) {
            return Optional.empty();
        }

        return Optional.of(session.getUserId());
    }
}
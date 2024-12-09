package cloud.ambar.common.sessionauth;

import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import jakarta.persistence.Id;
import lombok.*;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.data.mongodb.core.query.Criteria;
import org.springframework.data.mongodb.core.query.Query;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.time.Instant;
import java.util.Optional;

@Service
@RequestScope
@RequiredArgsConstructor
public class SessionRepository {
    private static final Logger log = LogManager.getLogger(SessionRepository.class);
    private final SessionConfig sessionConfig;
    private final MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator;
    public Optional<String> authenticatedUserIdFromSessionToken(String sessionToken) {
        Session session = mongoTransactionalProjectionOperator.operate().findOne(
                Query.query(
                        Criteria.where("sessionToken").is(sessionToken)
                ),
                Session.class,
                "AuthenticationForAllContexts_Session_Session"
        );

        if (session == null) {
            log.warn("Session not found for token: {}", sessionToken);
            return Optional.empty();
        }

        if (session.getSignedOut()) {
            log.warn("Session was signed out for token: {}", sessionToken);
            return Optional.empty();
        }

        if (session.getTokenLastRefreshedAt().isBefore(Instant.now().minusSeconds(sessionConfig.getSessionTokensExpireAfterSeconds()))) {
            log.warn("Session token was expired for token: {}", sessionToken);
            return Optional.empty();
        }

        return Optional.of(session.getUserId());
    }

    @Builder
    @AllArgsConstructor
    @Getter
    public static class Session {
        @Id
        @NonNull
        private String id;
        @NonNull private String userId;
        @NonNull private String sessionToken;
        @NonNull private Boolean signedOut;
        @NonNull private Instant tokenLastRefreshedAt;
    }
}
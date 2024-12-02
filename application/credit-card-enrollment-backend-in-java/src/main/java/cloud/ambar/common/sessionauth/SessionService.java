package cloud.ambar.common.sessionauth;

import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;

@Service
@RequiredArgsConstructor
public class SessionService {
    private static final Logger log = LogManager.getLogger(SessionService.class);
    private final SessionProjectionRepository sessionProjectionRepository;

    public String authenticatedUserIdFromSessionToken(final String sessionToken) {
        log.info("Looking up session details for token: " + sessionToken);
        return sessionProjectionRepository.getBySessionToken(sessionToken).orElseThrow().getUserId();
    }
}

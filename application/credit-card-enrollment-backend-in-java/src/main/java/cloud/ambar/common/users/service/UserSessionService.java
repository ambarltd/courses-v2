package cloud.ambar.common.users.service;

import cloud.ambar.common.users.models.UserSession;
import cloud.ambar.common.users.store.UserSessionProjectionRepository;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;

@Service
@RequiredArgsConstructor
public class UserSessionService {
    private static final Logger log = LogManager.getLogger(UserSessionService.class);
    private final UserSessionProjectionRepository userSessionProjectionRepository;

    public UserSession getUserSessionForToken(final String sessionToken) {
        log.info("Looking up session details for token: " + sessionToken);
        return userSessionProjectionRepository.getBySessionToken(sessionToken).orElseThrow();
    }
}

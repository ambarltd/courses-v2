package cloud.ambar.product.enrollment.controllers;

import cloud.ambar.common.users.models.UserSession;
import cloud.ambar.common.users.service.UserSessionService;
import cloud.ambar.product.enrollment.exceptions.InvalidUserException;
import cloud.ambar.product.enrollment.projection.models.EnrollmentRequest;
import cloud.ambar.product.enrollment.query.ProductEnrollmentQueryService;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestHeader;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;
import java.util.NoSuchElementException;

@RestController
@RequiredArgsConstructor
public class EnrollmentQueryController {
    private static final Logger log = LogManager.getLogger(EnrollmentQueryController.class);

    private final UserSessionService userSessionService;

    private final ProductEnrollmentQueryService productEnrollmentQueryService;

    private final ObjectMapper objectMapper;

    @PostMapping(value = "/api/v1/credit_card_enrollment/enrollment/list-enrollments")
    public String listItems(@RequestHeader("X-With-Session-Token") String sessionToken) throws JsonProcessingException {
        // Get the session token and make sure there is a user associated with it.
        final UserSession session = getSessionForToken(sessionToken);

        log.info("Listing enrollments for user: " + session.getUserId());
        List<EnrollmentRequest> requests = productEnrollmentQueryService.getUserEnrollmentRequests(session.getUserId());

        return objectMapper.writeValueAsString(requests);
    }

    private UserSession getSessionForToken(String sessionToken) {
        try {
            return userSessionService.getUserSessionForToken(sessionToken);
        } catch (NoSuchElementException e) {
            log.info("Unable to find valid session for token: " + sessionToken);
            throw new RuntimeException();
        }
    }
}
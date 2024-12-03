package cloud.ambar.creditcard.enrollment.controller;

import cloud.ambar.common.sessionauth.SessionService;
import cloud.ambar.creditcard.enrollment.projection.enrollmentlist.EnrollmentListItem;
import cloud.ambar.creditcard.enrollment.query.GetUserEnrollmentsQueryHandler;
import cloud.ambar.creditcard.enrollment.query.GetUserEnrollmentsQuery;
import lombok.RequiredArgsConstructor;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestHeader;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

@RestController
@RequiredArgsConstructor
public class EnrollmentQueryController {
    private final SessionService sessionService;

    private final GetUserEnrollmentsQueryHandler getUserEnrollmentsQueryHandler;

    @PostMapping(value = "/api/v1/credit_card/enrollment/list-enrollments")
    public List<EnrollmentListItem> listEnrollments(
            @RequestHeader("X-With-Session-Token") String sessionToken
    ) {
        final String userId = sessionService.authenticatedUserIdFromSessionToken(
                sessionService.authenticatedUserIdFromSessionToken(sessionToken)
        );

        GetUserEnrollmentsQuery query = GetUserEnrollmentsQuery.builder()
                .userId(userId)
                .build();

        return getUserEnrollmentsQueryHandler.handle(query);
    }
}
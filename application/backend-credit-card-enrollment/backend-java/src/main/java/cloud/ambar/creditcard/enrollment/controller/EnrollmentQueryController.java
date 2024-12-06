package cloud.ambar.creditcard.enrollment.controller;

import cloud.ambar.common.sessionauth.SessionService;
import cloud.ambar.creditcard.enrollment.projection.enrollmentlist.EnrollmentListItem;
import cloud.ambar.creditcard.enrollment.queryhandler.GetUserEnrollmentsQueryHandler;
import cloud.ambar.creditcard.enrollment.queryhandler.GetUserEnrollmentsQuery;
import lombok.RequiredArgsConstructor;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestHeader;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.context.annotation.RequestScope;

import java.util.List;

@RestController
@RequestScope
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
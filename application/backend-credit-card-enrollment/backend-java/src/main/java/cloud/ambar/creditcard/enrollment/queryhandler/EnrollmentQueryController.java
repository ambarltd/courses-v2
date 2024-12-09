package cloud.ambar.creditcard.enrollment.queryhandler;

import cloud.ambar.common.queryhandler.QueryController;
import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestHeader;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.context.annotation.RequestScope;

@RestController
@RequestScope
@RequestMapping("/api/v1/credit_card/enrollment")
public class EnrollmentQueryController extends QueryController {
    private final GetUserEnrollmentsQueryHandler getUserEnrollmentsQueryHandler;

    public EnrollmentQueryController(
            MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator,
            GetUserEnrollmentsQueryHandler getUserEnrollmentsQueryHandler
    ) {
        super(mongoTransactionalProjectionOperator);
        this.getUserEnrollmentsQueryHandler = getUserEnrollmentsQueryHandler;
    }

    @PostMapping(value = "/list-enrollments")
    public Object listEnrollments(
            @RequestHeader("X-With-Session-Token") String sessionToken
    ) {
        GetUserEnrollmentsQuery query = GetUserEnrollmentsQuery.builder()
                .sessionToken(sessionToken)
                .build();

        return processQuery(query, getUserEnrollmentsQueryHandler);
    }
}
package cloud.ambar.creditcard.enrollment.queryhandler;

import cloud.ambar.common.queryhandler.Query;
import cloud.ambar.common.queryhandler.QueryHandler;
import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import cloud.ambar.common.sessionauth.SessionService;
import cloud.ambar.creditcard.enrollment.projection.enrollmentlist.EnrollmentListItem;
import cloud.ambar.creditcard.enrollment.projection.enrollmentlist.GetEnrollmentList;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.util.List;

@Service
@RequestScope
public class GetUserEnrollmentsQueryHandler extends QueryHandler {
    private final SessionService sessionService;
    private final GetEnrollmentList getEnrollmentList;

    public GetUserEnrollmentsQueryHandler(
            MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator,
            SessionService sessionService,
            GetEnrollmentList getEnrollmentList
    ) {
        super(mongoTransactionalProjectionOperator);
        this.sessionService = sessionService;
        this.getEnrollmentList = getEnrollmentList;
    }

    @Override
    public Object handleQuery(Query query) {
        if (query instanceof GetUserEnrollmentsQuery) {
            return handleGetUserEnrollments((GetUserEnrollmentsQuery) query);
        } else {
            throw new IllegalArgumentException("Unsupported query type: " + query.getClass().getName());
        }
    }

    public List<EnrollmentListItem> handleGetUserEnrollments(final GetUserEnrollmentsQuery query) {
        String userId = sessionService.authenticatedUserIdFromSessionToken(query.getSessionToken());
        return getEnrollmentList.getEnrollmentList(userId);
    }
}

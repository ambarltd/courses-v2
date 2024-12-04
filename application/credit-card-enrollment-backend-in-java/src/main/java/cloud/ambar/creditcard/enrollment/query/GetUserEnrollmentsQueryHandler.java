package cloud.ambar.creditcard.enrollment.query;

import cloud.ambar.creditcard.enrollment.projection.enrollmentlist.EnrollmentListItem;
import cloud.ambar.creditcard.enrollment.projection.enrollmentlist.GetEnrollmentList;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.util.List;

@Service
@RequestScope
@RequiredArgsConstructor
public class GetUserEnrollmentsQueryHandler {
    private final GetEnrollmentList getEnrollmentList;

    public List<EnrollmentListItem> handle(final GetUserEnrollmentsQuery query) {
        return getEnrollmentList.getEnrollmentList(query.getUserId());
    }
}

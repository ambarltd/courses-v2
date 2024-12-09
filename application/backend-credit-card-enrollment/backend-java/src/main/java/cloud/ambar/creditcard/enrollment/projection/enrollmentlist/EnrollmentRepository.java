package cloud.ambar.creditcard.enrollment.projection.enrollmentlist;

import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import lombok.RequiredArgsConstructor;
import org.springframework.data.mongodb.core.query.Criteria;
import org.springframework.data.mongodb.core.query.Query;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.util.List;
import java.util.Optional;

@RequestScope
@Service
@RequiredArgsConstructor
public class EnrollmentRepository {
    private final MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator;

    public void save(final Enrollment enrollment) {
        mongoTransactionalProjectionOperator.operate().save(enrollment, "CreditCard_Enrollment_Enrollment");
    }

    public Optional<Enrollment> findOneById(final String id) {
        return Optional.ofNullable(mongoTransactionalProjectionOperator.operate().findOne(
                Query.query(
                        Criteria.where("id").is(id)
                ),
                Enrollment.class,
                "CreditCard_Enrollment_Enrollment"
        ));
    }

    public List<Enrollment> findAllByUserId(final String userId) {
        return mongoTransactionalProjectionOperator.operate().find(
                Query.query(
                        Criteria.where("userId").is(userId)
                ),
                Enrollment.class,
                "CreditCard_Enrollment_Enrollment"
        );
    }
}
package cloud.ambar.creditcard.enrollment.projection.enrollmentlist;

import org.springframework.data.mongodb.repository.MongoRepository;

import java.util.List;

public interface EnrollmentRepository extends MongoRepository<Enrollment, String> {
    List<Enrollment> findAllByUserId(final String userId);
}

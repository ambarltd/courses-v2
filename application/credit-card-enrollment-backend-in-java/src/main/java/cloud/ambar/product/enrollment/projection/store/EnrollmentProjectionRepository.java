package cloud.ambar.product.enrollment.projection.store;

import cloud.ambar.product.enrollment.projection.models.Enrollment;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface EnrollmentProjectionRepository extends MongoRepository<Enrollment, String> {}

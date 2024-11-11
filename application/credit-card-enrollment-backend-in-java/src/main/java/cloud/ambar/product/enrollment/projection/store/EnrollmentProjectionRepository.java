package cloud.ambar.product.enrollment.projection.store;

import cloud.ambar.product.enrollment.projection.models.EnrollmentStatus;
import cloud.ambar.product.management.projection.models.CreditCardProduct;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface EnrollmentProjectionRepository extends MongoRepository<EnrollmentStatus, String> {
}

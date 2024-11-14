package cloud.ambar.product.enrollment.projection.store;

import cloud.ambar.product.enrollment.projection.models.EnrollmentRequest;
import org.springframework.data.mongodb.repository.MongoRepository;

import java.util.List;

public interface EnrollmentProjectionRepository extends MongoRepository<EnrollmentRequest, String> {
    List<EnrollmentRequest> findAllByProductId(final String productId);
}

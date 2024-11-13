package cloud.ambar.product.enrollment.projection.store;

import cloud.ambar.product.enrollment.projection.models.CardProduct;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface EnrollmentCardProductProjectionRepository extends MongoRepository<CardProduct, String> {}

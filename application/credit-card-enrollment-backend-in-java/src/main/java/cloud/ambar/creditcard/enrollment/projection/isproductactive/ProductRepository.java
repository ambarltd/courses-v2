package cloud.ambar.creditcard.enrollment.projection.isproductactive;

import org.springframework.data.mongodb.repository.MongoRepository;

public interface ProductRepository extends MongoRepository<ProductActiveStatus, String> {}

package cloud.ambar.creditCardProduct.database.mongo;

import cloud.ambar.creditCardProduct.projection.models.CreditCardProduct;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface ProjectionRepository extends MongoRepository<CreditCardProduct, String> {
}

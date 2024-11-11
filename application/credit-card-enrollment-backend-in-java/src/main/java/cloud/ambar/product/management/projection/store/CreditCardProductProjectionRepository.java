package cloud.ambar.product.management.projection.store;

import cloud.ambar.product.management.projection.models.CreditCardProduct;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface CreditCardProductProjectionRepository extends MongoRepository<CreditCardProduct, String> {
}

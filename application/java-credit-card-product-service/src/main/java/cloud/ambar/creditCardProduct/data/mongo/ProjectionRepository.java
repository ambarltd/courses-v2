package cloud.ambar.creditCardProduct.data.mongo;

import cloud.ambar.creditCardProduct.data.models.projection.Product;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface ProjectionRepository extends MongoRepository<Product, String> {
    // void putItem(String id, String name, boolean active);
}

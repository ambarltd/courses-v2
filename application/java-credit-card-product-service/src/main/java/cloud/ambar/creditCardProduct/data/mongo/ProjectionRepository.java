package cloud.ambar.creditCardProduct.data.mongo;

import cloud.ambar.creditCardProduct.data.models.projection.Product;
import org.springframework.data.mongodb.repository.MongoRepository;

import java.util.Optional;

public interface ProjectionRepository extends MongoRepository<Product, String> {
}

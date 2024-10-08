package cloud.ambar.creditCardProduct.data.mongo;

import cloud.ambar.creditCardProduct.models.projection.ProductListItem;
import org.springframework.data.mongodb.repository.MongoRepository;

public interface ReadModelRepository extends MongoRepository<ProductListItem, String> {
    ProductListItem getItem(String id);
}

package cloud.ambar.creditCardProduct.data.mongo;

import cloud.ambar.creditCardProduct.models.projection.ProductListItem;
import org.springframework.data.mongodb.repository.MongoRepository;

import java.util.List;

public interface ReadModelRepository extends MongoRepository<ProductListItem, String> {
    List<ProductListItem> getAllItems();

    void putItem(String id, String name, boolean active);
}

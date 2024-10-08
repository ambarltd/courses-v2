package cloud.ambar.creditCardProduct.query;

import cloud.ambar.creditCardProduct.data.mongo.ReadModelRepository;
import cloud.ambar.creditCardProduct.models.projection.ProductListItem;
import org.springframework.stereotype.Service;

import java.util.List;

@Service
public class ListProductsQueryHandler {

    private final ReadModelRepository readModelRepository;

    public ListProductsQueryHandler(final ReadModelRepository readModelRepository) {
        this.readModelRepository = readModelRepository;
    }

    public List<ProductListItem> getAllProductListItems() {
        return readModelRepository.getAll();
    }

}

package cloud.ambar.creditCardProduct.query;

import cloud.ambar.creditCardProduct.data.mongo.ProjectionRepository;
import cloud.ambar.creditCardProduct.models.projection.ProductListItem;
import org.springframework.stereotype.Service;

import java.util.List;

@Service
public class QueryService {

    private final ProjectionRepository projectionRepository;

    public QueryService(final ProjectionRepository projectionRepository) {
        this.projectionRepository = projectionRepository;
    }

    public List<ProductListItem> getAllProductListItems() {
        return projectionRepository.findAll();
    }

}

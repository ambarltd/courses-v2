package cloud.ambar.creditCardProduct.query;

import cloud.ambar.creditCardProduct.data.mongo.ProjectionRepository;
import cloud.ambar.creditCardProduct.data.models.projection.Product;
import org.springframework.stereotype.Service;

import java.util.List;

@Service
public class QueryService {

    private final ProjectionRepository projectionRepository;

    public QueryService(final ProjectionRepository projectionRepository) {
        this.projectionRepository = projectionRepository;
    }

    public List<Product> getAllProductListItems() {
        return projectionRepository.findAll();
    }

}

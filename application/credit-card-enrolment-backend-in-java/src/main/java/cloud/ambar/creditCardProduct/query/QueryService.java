package cloud.ambar.creditCardProduct.query;

import cloud.ambar.creditCardProduct.database.mongo.ProjectionRepository;
import cloud.ambar.creditCardProduct.projection.models.CreditCardProduct;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

import java.util.List;

@Service
@RequiredArgsConstructor
public class QueryService {

    private final ProjectionRepository projectionRepository;

    public List<CreditCardProduct> getAllCreditCardProducts() {
        return projectionRepository.findAll();
    }

}

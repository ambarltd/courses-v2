package cloud.ambar.product.management.query;

import cloud.ambar.product.management.projection.store.CreditCardProductProjectionRepository;
import cloud.ambar.product.management.projection.models.CreditCardProduct;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

import java.util.List;

@Service
@RequiredArgsConstructor
public class QueryService {

    private final CreditCardProductProjectionRepository creditCardProductProjectionRepository;

    public List<CreditCardProduct> getAllCreditCardProducts() {
        return creditCardProductProjectionRepository.findAll();
    }

}

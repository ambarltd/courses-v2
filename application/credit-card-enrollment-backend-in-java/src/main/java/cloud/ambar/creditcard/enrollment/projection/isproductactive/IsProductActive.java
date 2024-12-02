package cloud.ambar.creditcard.enrollment.projection.isproductactive;

import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

@Service
@RequiredArgsConstructor
public class IsProductActive {
    private final ProductRepository productRepository;

    public boolean isProductActive(final String productId) {
        return productRepository.findById(productId)
                .map(ProductActiveStatus::getActive)
                .orElse(false);
    }
}

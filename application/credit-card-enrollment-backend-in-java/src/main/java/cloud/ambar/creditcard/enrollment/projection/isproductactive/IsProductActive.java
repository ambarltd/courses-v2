package cloud.ambar.creditcard.enrollment.projection.isproductactive;

import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

@Service
@RequestScope
@RequiredArgsConstructor
public class IsProductActive {
    private final ProductActiveStatusRepository productActiveStatusRepository;

    public boolean isProductActive(final String productId) {
        return productActiveStatusRepository.isThereAnActiveProductWithId(productId);
    }
}

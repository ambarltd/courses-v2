package cloud.ambar.creditcard.enrollment.projection.isproductactive;

import cloud.ambar.common.event.Event;
import cloud.ambar.common.projection.ProjectionHandler;
import cloud.ambar.creditcard.product.event.ProductActivated;
import cloud.ambar.creditcard.product.event.ProductDeactivated;
import cloud.ambar.creditcard.product.event.ProductDefined;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

@Service
@RequiredArgsConstructor
public class IsProductActiveProjectionHandler extends ProjectionHandler {
    private final ProductRepository productRepository;
    public void project(final Event event) {
        if (event instanceof ProductDefined) {
            productRepository.save(ProductActiveStatus.builder()
                    .id(event.getAggregateId())
                    .active(false)
                    .build());
        } else if (event instanceof ProductActivated) {
            ProductActiveStatus productActiveStatus = productRepository.findById(event.getAggregateId()).orElseThrow();
            productActiveStatus.setActive(true);
            productRepository.save(productActiveStatus);
        } else if (event instanceof ProductDeactivated) {
            ProductActiveStatus productActiveStatus = productRepository.findById(event.getAggregateId()).orElseThrow();
            productActiveStatus.setActive(false);
            productRepository.save(productActiveStatus);
        }
    }
}

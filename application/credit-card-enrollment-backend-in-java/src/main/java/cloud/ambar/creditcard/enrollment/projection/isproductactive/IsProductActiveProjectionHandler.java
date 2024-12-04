package cloud.ambar.creditcard.enrollment.projection.isproductactive;

import cloud.ambar.common.event.Event;
import cloud.ambar.common.projection.ProjectionHandler;
import cloud.ambar.creditcard.product.event.ProductActivated;
import cloud.ambar.creditcard.product.event.ProductDeactivated;
import cloud.ambar.creditcard.product.event.ProductDefined;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

@Service
@RequestScope
@RequiredArgsConstructor
public class IsProductActiveProjectionHandler extends ProjectionHandler {
    private final ProductActiveStatusRepository productActiveStatusRepository;

    public void project(final Event event) {
        if (event instanceof ProductDefined) {
            productActiveStatusRepository.save(ProductActiveStatus.builder()
                    .id(event.getAggregateId())
                    .active(false)
                    .build());
        } else if (event instanceof ProductActivated) {
            ProductActiveStatus productActiveStatus = productActiveStatusRepository.findOneById(event.getAggregateId()).orElseThrow();
            productActiveStatus.setActive(true);
            productActiveStatusRepository.save(productActiveStatus);
        } else if (event instanceof ProductDeactivated) {
            ProductActiveStatus productActiveStatus = productActiveStatusRepository.findOneById(event.getAggregateId()).orElseThrow();
            productActiveStatus.setActive(false);
            productActiveStatusRepository.save(productActiveStatus);
        }
    }
}

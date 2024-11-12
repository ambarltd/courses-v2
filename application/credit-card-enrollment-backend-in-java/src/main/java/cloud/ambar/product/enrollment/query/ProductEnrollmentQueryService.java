package cloud.ambar.product.enrollment.query;

import cloud.ambar.product.enrollment.projection.models.CardProduct;
import cloud.ambar.product.enrollment.projection.models.Enrollment;
import cloud.ambar.product.enrollment.projection.store.CardProductProjectionRepository;
import cloud.ambar.product.enrollment.projection.store.EnrollmentProjectionRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

import java.util.Optional;

@Service
@RequiredArgsConstructor
public class ProductEnrollmentQueryService {
    private final CardProductProjectionRepository cardProductProjectionRepository;
    private final EnrollmentProjectionRepository enrollmentProjectionRepository;

    public Optional<CardProduct> getCardProduct(final String id) {
        return cardProductProjectionRepository.findById(id);
    }

    public Optional<Enrollment> getEnrollment(final String id) {
        return enrollmentProjectionRepository.findById(id);
    }
}

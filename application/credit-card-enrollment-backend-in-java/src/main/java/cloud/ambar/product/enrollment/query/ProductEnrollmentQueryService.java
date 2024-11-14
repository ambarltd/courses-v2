package cloud.ambar.product.enrollment.query;

import cloud.ambar.product.enrollment.projection.models.CardProduct;
import cloud.ambar.product.enrollment.projection.models.EnrollmentRequest;
import cloud.ambar.product.enrollment.projection.store.EnrollmentCardProductProjectionRepository;
import cloud.ambar.product.enrollment.projection.store.EnrollmentProjectionRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

import java.util.List;
import java.util.Optional;

@Service
@RequiredArgsConstructor
public class ProductEnrollmentQueryService {
    private final EnrollmentCardProductProjectionRepository enrollmentCardProductProjectionRepository;
    private final EnrollmentProjectionRepository enrollmentProjectionRepository;

    public Optional<CardProduct> getCardProduct(final String id) {
        return enrollmentCardProductProjectionRepository.findById(id);
    }

    public Optional<EnrollmentRequest> getEnrollment(final String id) {
        return enrollmentProjectionRepository.findById(id);
    }

    public List<EnrollmentRequest> getUserEnrollmentRequests(final String userId) {
        return enrollmentProjectionRepository.findAllByUserId(userId);
    }
}

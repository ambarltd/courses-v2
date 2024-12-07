package cloud.ambar.creditcard.enrollment.projection.enrollmentlist;

import cloud.ambar.creditcard.enrollment.aggregate.EnrollmentStatus;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.util.List;
import java.util.stream.Collectors;

@Service
@RequestScope
@RequiredArgsConstructor
public class GetEnrollmentList {
    private final EnrollmentRepository enrollmentRepository;
    private final ProductNameRepository productNameRepository;
    public List<EnrollmentListItem> getEnrollmentList(final String userId) {
        return enrollmentRepository.findAllByUserId(userId)
                .stream()
                .map(enrollment -> EnrollmentListItem.builder()
                        .id(enrollment.getId())
                        .userId(enrollment.getUserId())
                        .productId(enrollment.getProductId())
                        .productName(
                                productNameRepository.findOneById(enrollment.getProductId())
                                        .orElseThrow()
                                        .getName())
                        .requestedDate(enrollment.getRequestedDate())
                        .status(enrollment.getStatus())
                        .statusReason(enrollment.getStatusReason())
                        .reviewedOn(enrollment.getReviewedOn())
                        .build())
                .collect(Collectors.toList());
    }

    public boolean isThereAnyAcceptedEnrollmentForUserAndProduct(final String userId, final String productId) {
        return enrollmentRepository.findAllByUserId(userId)
                .stream()
                .anyMatch(enrollment -> enrollment.getProductId().equals(productId) && enrollment.getStatus().equals(EnrollmentStatus.ACCEPTED.name()));
    }
}
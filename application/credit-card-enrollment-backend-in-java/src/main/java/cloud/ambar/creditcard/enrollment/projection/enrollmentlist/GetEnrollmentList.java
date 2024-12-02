package cloud.ambar.creditcard.enrollment.projection.enrollmentlist;

import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

import java.util.List;
import java.util.stream.Collectors;

@Service
@RequiredArgsConstructor
public class GetEnrollmentList {
    private final EnrollmentRepository enrollmentRepository;
    private final ProductNameRepository productNameRepository;
    public List<EnrollmentListItem> getEnrollmentList(final String userId) {
        return enrollmentRepository.findAllByUserId(userId)
                .stream()
                .map(enrollment -> EnrollmentListItem.builder()
                        .userId(enrollment.getUserId())
                        .productId(enrollment.getProductId())
                        .productName(
                                productNameRepository.findById(enrollment.getProductId())
                                        .orElseThrow()
                                        .getName())
                        .requestedDate(enrollment.getRequestedDate())
                        .status(enrollment.getStatus())
                        .reviewedDate(enrollment.getReviewedDate())
                        .build())
                .collect(Collectors.toList());
    }
}
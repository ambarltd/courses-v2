package cloud.ambar.creditcard.enrollment.projection.enrollmentlist;

import cloud.ambar.common.event.Event;
import cloud.ambar.common.projection.ProjectionHandler;
import cloud.ambar.creditcard.enrollment.aggregate.EnrollmentStatus;
import cloud.ambar.creditcard.enrollment.event.EnrollmentAccepted;
import cloud.ambar.creditcard.enrollment.event.EnrollmentDeclined;
import cloud.ambar.creditcard.enrollment.event.EnrollmentRequested;
import cloud.ambar.creditcard.product.event.ProductDefined;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

@Service
@RequestScope
@RequiredArgsConstructor
public class EnrollmentListProjectionHandler extends ProjectionHandler {
    private final EnrollmentRepository enrollmentRepository;
    private final ProductNameRepository productNameRepository;

    protected void project(Event event) {
        if (event instanceof ProductDefined) {
            productNameRepository.save(ProductName.builder()
                    .id(event.getAggregateId())
                    .name(((ProductDefined) event).getName())
                    .build());
        } else if (event instanceof EnrollmentRequested) {
            enrollmentRepository.save(Enrollment.builder()
                    .id(event.getAggregateId())
                    .userId(((EnrollmentRequested) event).getUserId())
                    .productId(((EnrollmentRequested) event).getProductId())
                    .requestedDate(event.getRecordedOn())
                    .status(EnrollmentStatus.REQUESTED.name())
                    .build());
        } else if (event instanceof EnrollmentAccepted) {
            Enrollment enrollment = enrollmentRepository.findOneById(event.getAggregateId()).orElseThrow();
            enrollment.setStatus(EnrollmentStatus.ACCEPTED.name());
            enrollment.setReviewedOn(event.getRecordedOn());
            enrollment.setStatusReason(((EnrollmentAccepted) event).getReasonDescription());
            enrollmentRepository.save(enrollment);
        } else if (event instanceof EnrollmentDeclined) {
            Enrollment enrollment = enrollmentRepository.findOneById(event.getAggregateId()).orElseThrow();
            enrollment.setStatus(EnrollmentStatus.DECLINED.name());
            enrollment.setReviewedOn(event.getRecordedOn());
            enrollment.setStatusReason(((EnrollmentDeclined) event).getReasonDescription());
            enrollmentRepository.save(enrollment);
        }
    }
}

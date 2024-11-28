package cloud.ambar.product.enrollment.projection.service;

import cloud.ambar.common.ambar.event.Payload;
import cloud.ambar.common.exceptions.UnexpectedEventException;
import cloud.ambar.common.projection.Projector;
import cloud.ambar.product.enrollment.aggregate.EnrollmentStatus;
import cloud.ambar.product.enrollment.events.EnrollmentAcceptedEventData;
import cloud.ambar.product.enrollment.events.EnrollmentDeclinedEventData;
import cloud.ambar.product.enrollment.events.EnrollmentPendingReviewEventData;
import cloud.ambar.product.enrollment.events.EnrollmentRequestedEventData;
import cloud.ambar.product.enrollment.projection.models.CardProduct;
import cloud.ambar.product.enrollment.projection.models.EnrollmentRequest;
import cloud.ambar.product.enrollment.projection.store.EnrollmentCardProductProjectionRepository;
import cloud.ambar.product.enrollment.projection.store.EnrollmentProjectionRepository;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;

import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;
import java.util.Optional;

@Service
@RequiredArgsConstructor
public class EnrollmentProjectionService implements Projector {
    private static final Logger log = LogManager.getLogger(EnrollmentProjectionService.class);

    private final EnrollmentProjectionRepository enrollmentProjectionRepository;
    private final EnrollmentCardProductProjectionRepository enrollmentCardProductProjectionRepository;

    private final ObjectMapper objectMapper;

    @Override
    public void project(Payload event) throws JsonProcessingException {
        final EnrollmentRequest enrollment;
        switch (event.getEventName()) {
            case EnrollmentRequestedEventData.EVENT_NAME -> {
                final EnrollmentRequestedEventData eventData = objectMapper.readValue(event.getData(), EnrollmentRequestedEventData.class);
                final CardProduct p = getProductDetails(eventData.getProductId());
                enrollment = new EnrollmentRequest();
                final String id = event.getAggregateId();
                enrollment.setId(id);
                enrollment.setProductName(p.getName());
                enrollment.setUserId(eventData.getUserId());
                enrollment.setProductId(eventData.getProductId());
                enrollment.setStatus(EnrollmentStatus.REQUESTED.name());
                enrollment.setRequestedDate(event.getRecordedOn().format(DateTimeFormatter.RFC_1123_DATE_TIME));
            }
            case EnrollmentPendingReviewEventData.EVENT_NAME -> {
                enrollment = getAndSetStatus(event.getAggregateId(), EnrollmentStatus.REQUESTED);
            }
            case EnrollmentAcceptedEventData.EVENT_NAME -> {
                enrollment = getAndSetStatus(event.getAggregateId(), EnrollmentStatus.ACCEPTED);
            }
            case EnrollmentDeclinedEventData.EVENT_NAME -> {
                final EnrollmentDeclinedEventData eventData = objectMapper.readValue(event.getData(), EnrollmentDeclinedEventData.class);
                enrollment = getAndSetStatus(event.getAggregateId(), EnrollmentStatus.DECLINED);
                enrollment.setStatusCode(eventData.getReasonCode());
                enrollment.setStatusReason(eventData.getReasonDescription());
            }
            // For now Ambar is sending all events. But we could update the filter to only give us events related to
            // the properties of products which we actually display.
            // Throwing this will tell ambar to keep going despite something unexpected.
            default -> throw new UnexpectedEventException(event.getEventName());
        }

        enrollmentProjectionRepository.save(enrollment);
    }

    private CardProduct getProductDetails(String productId) {
        Optional<CardProduct> cardProduct = enrollmentCardProductProjectionRepository.findById(productId);
        if (cardProduct.isEmpty()) {
            final String msg = "Unable to find CardProduct in projection repository for id: " + productId;
            log.error(msg);
            throw new RuntimeException(msg);
        }
        return cardProduct.get();
    }

    private EnrollmentRequest getAndSetStatus(String id, EnrollmentStatus status) {
        final EnrollmentRequest enrollment = getOrThrow(id);
        enrollment.setStatus(status.name());
        switch (status) {
            case ACCEPTED, DECLINED -> enrollment.setReviewedDate(OffsetDateTime.now().format(DateTimeFormatter.RFC_1123_DATE_TIME));
        }
        return enrollment;
    }

    private EnrollmentRequest getOrThrow(String id) {
        Optional<EnrollmentRequest> enrollment = enrollmentProjectionRepository.findById(id);
        if (enrollment.isEmpty()) {
            final String msg = "Unable to find Enrollment in projection repository for id: " + id;
            log.error(msg);
            throw new RuntimeException(msg);
        }
        return enrollment.get();
    }
}

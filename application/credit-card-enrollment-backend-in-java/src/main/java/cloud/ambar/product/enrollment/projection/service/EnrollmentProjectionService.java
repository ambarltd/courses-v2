package cloud.ambar.product.enrollment.projection.service;

import cloud.ambar.common.ambar.event.Payload;
import cloud.ambar.common.exceptions.UnexpectedEventException;
import cloud.ambar.common.projection.Projector;
import cloud.ambar.product.enrollment.aggregate.EnrollmentStatus;
import cloud.ambar.product.enrollment.events.EnrollmentAcceptedEventData;
import cloud.ambar.product.enrollment.events.EnrollmentDeclinedEventData;
import cloud.ambar.product.enrollment.events.EnrollmentPendingReviewEventData;
import cloud.ambar.product.enrollment.events.EnrollmentRequestedEventData;
import cloud.ambar.product.enrollment.projection.models.EnrollmentRequest;
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

    private final ObjectMapper objectMapper;

    @Override
    public void project(Payload event) throws JsonProcessingException {
        final EnrollmentRequest enrollment;
        switch (event.getEventName()) {
            case EnrollmentRequestedEventData.EVENT_NAME -> {
                final EnrollmentRequestedEventData eventData = objectMapper.readValue(event.getData(), EnrollmentRequestedEventData.class);
                enrollment = new EnrollmentRequest();
                final String id = eventData.getUserId() + "-" + eventData.getProductId();
                enrollment.setId(id);
                enrollment.setUserId(eventData.getUserId());
                enrollment.setProductId(eventData.getProductId());
                enrollment.setStatus(EnrollmentStatus.REQUESTED.name());
                enrollment.setRequestedDate(event.getRecordedOn().format(DateTimeFormatter.RFC_1123_DATE_TIME));
            }
            case EnrollmentPendingReviewEventData.EVENT_NAME -> {
                final EnrollmentPendingReviewEventData eventData = objectMapper.readValue(event.getData(), EnrollmentPendingReviewEventData.class);
                enrollment = getAndSetStatus(eventData.getId(), EnrollmentStatus.REQUESTED);
            }
            case EnrollmentAcceptedEventData.EVENT_NAME -> {
                final EnrollmentAcceptedEventData eventData = objectMapper.readValue(event.getData(), EnrollmentAcceptedEventData.class);
                enrollment = getAndSetStatus(eventData.getId(), EnrollmentStatus.ACCEPTED);
            }
            case EnrollmentDeclinedEventData.EVENT_NAME -> {
                final EnrollmentDeclinedEventData eventData = objectMapper.readValue(event.getData(), EnrollmentDeclinedEventData.class);
                enrollment = getAndSetStatus(eventData.getId(), EnrollmentStatus.DECLINED);
            }
            // For now Ambar is sending all events. But we could update the filter to only give us events related to
            // the properties of products which we actually display.
            // Throwing this will tell ambar to keep going despite something unexpected.
            default -> throw new UnexpectedEventException(event.getEventName());
        }

        enrollmentProjectionRepository.save(enrollment);
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

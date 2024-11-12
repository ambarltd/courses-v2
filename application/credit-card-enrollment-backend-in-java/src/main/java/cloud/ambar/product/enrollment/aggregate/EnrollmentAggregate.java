package cloud.ambar.product.enrollment.aggregate;

import cloud.ambar.common.aggregate.Aggregate;
import cloud.ambar.common.event.Event;
import cloud.ambar.product.enrollment.events.EnrollmentAcceptedEventData;
import cloud.ambar.product.enrollment.events.EnrollmentDeclinedEventData;
import cloud.ambar.product.enrollment.events.EnrollmentPendingReviewEventData;
import cloud.ambar.product.enrollment.events.EnrollmentRequestedEventData;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.Data;
import lombok.EqualsAndHashCode;
import lombok.NoArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

@EqualsAndHashCode(callSuper = true)
@Data
@NoArgsConstructor
public class EnrollmentAggregate extends Aggregate {
    private static final Logger log = LogManager.getLogger(EnrollmentAggregate.class);

    private String userId;
    private String productId;
    private String status;

    public EnrollmentAggregate(String aggregateId) {
        super(aggregateId);
    }

    @Override
    public void transform(Event event) {
        ObjectMapper om = new ObjectMapper();
        switch (event.getEventName()) {
            case EnrollmentRequestedEventData.EVENT_NAME -> {
                try {
                    EnrollmentRequestedEventData enrollment = om.readValue(event.getData(), EnrollmentRequestedEventData.class);
                    this.userId = enrollment.getUserId();
                    this.productId = enrollment.getProductId();
                    this.status = EnrollmentStatus.REQUESTED.name();
                } catch (JsonProcessingException e) {
                    log.error("Error creating initial definition from event!");
                    throw new RuntimeException("Error processing EnrollmentRequestedEvent");
                }
            }
            case EnrollmentPendingReviewEventData.EVENT_NAME -> this.status = EnrollmentStatus.PENDING.name();
            case EnrollmentAcceptedEventData.EVENT_NAME      -> this.status = EnrollmentStatus.ACCEPTED.name();
            case EnrollmentDeclinedEventData.EVENT_NAME      -> this.status = EnrollmentStatus.DECLINED.name();

        }
    }
}

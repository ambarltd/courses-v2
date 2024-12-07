package cloud.ambar.common.serializedevent;

import cloud.ambar.common.event.Event;
import cloud.ambar.creditcard.enrollment.event.EnrollmentAccepted;
import cloud.ambar.creditcard.enrollment.event.EnrollmentDeclined;
import cloud.ambar.creditcard.enrollment.event.EnrollmentRequested;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.node.ObjectNode;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

import java.time.Instant;
import java.time.ZoneId;
import java.time.ZonedDateTime;
import java.time.format.DateTimeFormatter;

@Service
@RequiredArgsConstructor
public class Serializer {
    private final ObjectMapper objectMapper = new ObjectMapper();

    public SerializedEvent serialize(Event event) {
        return SerializedEvent.builder()
                .eventId(event.getEventId())
                .aggregateId(event.getAggregateId())
                .aggregateVersion(event.getAggregateVersion())
                .correlationId(event.getCorrelationId())
                .causationId(event.getCausationId())
                .recordedOn(formatInstant(event.getRecordedOn()))
                .eventName(determineEventName(event))
                .jsonPayload(createJsonPayload(event))
                .jsonMetadata("{}")
                .build();
    }

    private String determineEventName(Event event) {
        return switch (event) {
            case EnrollmentRequested _event -> "CreditCard_Enrollment_EnrollmentRequested";
            case EnrollmentAccepted _event -> "CreditCard_Enrollment_EnrollmentAccepted";
            case EnrollmentDeclined _event -> "CreditCard_Enrollment_EnrollmentDeclined";
            case null -> throw new RuntimeException("Event is null");
            default -> {
                throw new RuntimeException("Unknown event type: " + event.getClass().getName());
            }
        };
    }

    private String createJsonPayload(Event event) {
        try {
            ObjectNode jsonPayload = objectMapper.createObjectNode();

            if (event instanceof EnrollmentRequested enrollmentRequested) {
                jsonPayload.put("annualIncomeInCents", enrollmentRequested.getAnnualIncomeInCents());
                jsonPayload.put("productId", enrollmentRequested.getProductId());
                jsonPayload.put("userId", enrollmentRequested.getUserId());
            } else if (event instanceof EnrollmentAccepted enrollmentAccepted) {
                jsonPayload.put("reasonCode", enrollmentAccepted.getReasonCode());
                jsonPayload.put("reasonDescription", enrollmentAccepted.getReasonDescription());
            }  else if (event instanceof EnrollmentDeclined enrollmentDeclined) {
                jsonPayload.put("reasonCode", enrollmentDeclined.getReasonCode());
                jsonPayload.put("reasonDescription", enrollmentDeclined.getReasonDescription());
            } else {
                return "{}";
            }

            return objectMapper.writeValueAsString(jsonPayload);
        } catch (Exception e) {
            throw new RuntimeException("Failed to serialize event payload", e);
        }
    }

    private String formatInstant(Instant instant) {
        ZonedDateTime zdt = instant.atZone(ZoneId.of("UTC"));
        return DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss.SSSSSS z").format(zdt);
    }
}
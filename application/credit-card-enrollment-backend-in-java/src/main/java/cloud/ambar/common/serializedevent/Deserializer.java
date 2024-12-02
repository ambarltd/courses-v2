package cloud.ambar.common.serializedevent;

import cloud.ambar.common.event.Event;
import cloud.ambar.creditcard.enrollment.event.EnrollmentDeclined;
import cloud.ambar.creditcard.enrollment.event.EnrollmentSubmittedForReview;
import cloud.ambar.creditcard.enrollment.event.EnrollmentRequested;
import cloud.ambar.creditcard.product.event.ProductActivated;
import cloud.ambar.creditcard.product.event.ProductDeactivated;
import cloud.ambar.creditcard.product.event.ProductDefined;
import com.fasterxml.jackson.databind.ObjectMapper;
import org.springframework.stereotype.Service;

import java.time.Instant;
import java.time.ZonedDateTime;
import java.time.format.DateTimeFormatter;

@Service
public class Deserializer {
    public Event deserialize(SerializedEvent serializedEvent) {
        return switch (serializedEvent.getEventName()) {
            case "CreditCard_Enrollment_EnrollmentRequested" -> EnrollmentRequested.builder()
                    .eventId(serializedEvent.getEventId())
                    .aggregateId(serializedEvent.getAggregateId())
                    .aggregateVersion(serializedEvent.getAggregateVersion())
                    .correlationId(serializedEvent.getCorrelationId())
                    .causationId(serializedEvent.getCausationId())
                    .recordedOn(toInstant(serializedEvent.getRecordedOn()))
                    .annualIncomeInCents(payloadInt(serializedEvent.getJsonPayload(), "annualIncomeInCents"))
                    .productId(payloadString(serializedEvent.getJsonPayload(), "productId"))
                    .userId(payloadString(serializedEvent.getJsonPayload(), "userId"))
                    .build();
            case "CreditCard_Enrollment_EnrollmentSubmittedForReview" -> EnrollmentSubmittedForReview.builder()
                    .eventId(serializedEvent.getEventId())
                    .aggregateId(serializedEvent.getAggregateId())
                    .aggregateVersion(serializedEvent.getAggregateVersion())
                    .correlationId(serializedEvent.getCorrelationId())
                    .causationId(serializedEvent.getCausationId())
                    .recordedOn(toInstant(serializedEvent.getRecordedOn()))
                    .build();
            case "CreditCard_Enrollment_EnrollmentDeclined" -> EnrollmentDeclined.builder()
                    .eventId(serializedEvent.getEventId())
                    .aggregateId(serializedEvent.getAggregateId())
                    .aggregateVersion(serializedEvent.getAggregateVersion())
                    .correlationId(serializedEvent.getCorrelationId())
                    .causationId(serializedEvent.getCausationId())
                    .recordedOn(toInstant(serializedEvent.getRecordedOn()))
                    .reasonCode(payloadString(serializedEvent.getJsonPayload(), "reasonCode"))
                    .reasonDescription(payloadString(serializedEvent.getJsonPayload(), "reasonDescription"))
                    .build();
            case "CreditCard_Product_ProductActivated" -> ProductActivated.builder()
                    .eventId(serializedEvent.getEventId())
                    .aggregateId(serializedEvent.getAggregateId())
                    .aggregateVersion(serializedEvent.getAggregateVersion())
                    .correlationId(serializedEvent.getCorrelationId())
                    .causationId(serializedEvent.getCausationId())
                    .recordedOn(toInstant(serializedEvent.getRecordedOn()))
                    .build();
            case "CreditCard_Product_ProductDeactivated" -> ProductDeactivated.builder()
                    .eventId(serializedEvent.getEventId())
                    .aggregateId(serializedEvent.getAggregateId())
                    .aggregateVersion(serializedEvent.getAggregateVersion())
                    .correlationId(serializedEvent.getCorrelationId())
                    .causationId(serializedEvent.getCausationId())
                    .recordedOn(toInstant(serializedEvent.getRecordedOn()))
                    .build();
            case "CreditCard_Product_ProductDefined" -> ProductDefined.builder()
                    .eventId(serializedEvent.getEventId())
                    .aggregateId(serializedEvent.getAggregateId())
                    .aggregateVersion(serializedEvent.getAggregateVersion())
                    .correlationId(serializedEvent.getCorrelationId())
                    .causationId(serializedEvent.getCausationId())
                    .recordedOn(toInstant(serializedEvent.getRecordedOn()))
                    .name(payloadString(serializedEvent.getJsonPayload(), "name"))
                    .interestInBasisPoints(payloadInt(serializedEvent.getJsonPayload(), "interestInBasisPoints"))
                    .annualFeeInCents(payloadInt(serializedEvent.getJsonPayload(), "annualFeeInCents"))
                    .paymentCycle(payloadString(serializedEvent.getJsonPayload(), "paymentCycle"))
                    .creditLimitInCents(payloadInt(serializedEvent.getJsonPayload(), "creditLimitInCents"))
                    .maxBalanceTransferAllowedInCents(payloadInt(serializedEvent.getJsonPayload(), "maxBalanceTransferAllowedInCents"))
                    .reward(payloadString(serializedEvent.getJsonPayload(), "reward"))
                    .cardBackgroundHex(payloadString(serializedEvent.getJsonPayload(), "cardBackgroundHex"))
                    .build();
            default -> throw new RuntimeException("Unknown event type: " + serializedEvent.getEventName());
        };
    }


    private Instant toInstant(String recordedOn) {
        DateTimeFormatter formatter = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss.SSSSSS z");
        return ZonedDateTime.parse(recordedOn, formatter).toInstant();
    }

    private String payloadString(String jsonString, String fieldName) {
        final ObjectMapper objectMapper = new ObjectMapper();

        try {
            return objectMapper.readTree(jsonString).get(fieldName).asText();
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    private int payloadInt(String jsonString, String fieldName) {
        final ObjectMapper objectMapper = new ObjectMapper();

        try {
            return objectMapper.readTree(jsonString).get(fieldName).asInt();
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }
}

package cloud.ambar.common.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Index;
import jakarta.persistence.Table;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
@Entity
@Table(name = "event_store", indexes = {
        @Index(name = "event_store_idx_event_aggregate_id_version", columnList = "aggregate_id, aggregate_version", unique = true),
        @Index(name = "event_store_idx_event_causation_id", columnList = "causation_id", unique = true),
        @Index(name = "event_store_idx_event_correlation_id", columnList = "correlation_id"),
        @Index(name = "event_store_idx_occurred_on", columnList = "recorded_on")
})
@JsonIgnoreProperties(ignoreUnknown = true)
public class Event {
    @Id
    @GeneratedValue(strategy= GenerationType.IDENTITY)
    @JsonProperty("id")
    private Long id;

    @Column(name="event_id")
    @JsonProperty("event_id")
    private String eventId;

    @Column(name="aggregate_id")
    @JsonProperty("aggregate_id")
    private String aggregateId;

    @Column(name="causation_id")
    @JsonProperty("causation_id")
    private String causationID;

    @Column(name="correlation_id")
    @JsonProperty("correlation_id")
    private String correlationId;

    @Column(name="aggregate_version")
    @JsonProperty("aggregate_version")
    private long version;

    @Column(name="json_payload")
    @JsonProperty("json_payload")
    private String data;

    @Column(name="json_metadata")
    @JsonProperty("json_metadata")
    private String metadata;

    @Column(name="recorded_on")
    @JsonProperty("recorded_on")
    private LocalDateTime timeStamp;

    @Column(name="event_name")
    @JsonProperty("event_name")
    private String eventName;
}

package cloud.ambar.common.models;

import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;
import java.util.UUID;

@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
@Entity
@Table(name = "event_store")
public class Event {
    @Id
    private UUID id;
    private UUID aggregateId;
    private UUID causationID;
    private UUID correlationId;
    private String eventType;
    private String aggregateType;
    private long version;
    // TBD do we need both of these byte arrays?
    private byte[] data;
    private byte[] metaData;
    private LocalDateTime timeStamp;

    public Event(String eventType, String aggregateType) {
        this.id = UUID.randomUUID();
        this.eventType = eventType;
        this.aggregateType = aggregateType;
        this.timeStamp = LocalDateTime.now();
    }
}

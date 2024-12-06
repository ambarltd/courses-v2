package cloud.ambar.common.eventstore;

import cloud.ambar.common.serializedevent.SerializedEvent;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.util.List;
import java.util.Optional;

@Service
@RequestScope
public interface EventRepository extends JpaRepository<SerializedEvent, Long> {
    List<SerializedEvent> findAllByAggregateId(String aggregateId);
    Optional<SerializedEvent> findByEventId(String eventId);
}

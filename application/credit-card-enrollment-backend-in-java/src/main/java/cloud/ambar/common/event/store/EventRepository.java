package cloud.ambar.common.event.store;

import cloud.ambar.common.event.Event;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;
import java.util.Optional;

public interface EventRepository extends JpaRepository<Event, Long> {
    List<Event> findAllByAggregateId(String aggregateId);
    Optional<Event> findByEventId(String eventId);
}

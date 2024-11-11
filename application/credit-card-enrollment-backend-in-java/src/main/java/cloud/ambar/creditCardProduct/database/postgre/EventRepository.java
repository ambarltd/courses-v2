package cloud.ambar.creditCardProduct.database.postgre;

import cloud.ambar.creditCardProduct.events.Event;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

public interface EventRepository extends JpaRepository<Event, Long> {
    List<Event> findAllByAggregateId(String aggregateId);
    Optional<Event> findByEventId(String eventId);
}

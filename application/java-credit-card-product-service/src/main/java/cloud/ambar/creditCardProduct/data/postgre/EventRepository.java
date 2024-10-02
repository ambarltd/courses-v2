package cloud.ambar.creditCardProduct.data.postgre;

import cloud.ambar.common.models.Event;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

// @Repository is not strictly required. Added here for clarity.
@Repository
public interface EventRepository extends JpaRepository<Event, Long> {
    List<Event> findAllByAggregateId(String aggregateId);
    Optional<Event> findByEventId(String eventId);
}

package cloud.ambar.creditCardProduct.data.postgre;

import cloud.ambar.common.models.Event;
import org.springframework.data.jpa.repository.JpaRepository;


public interface EventRepository extends JpaRepository<Event, Long>{
}

package cloud.ambar.product.enrollment.aggregate;

import cloud.ambar.common.aggregate.Aggregate;
import cloud.ambar.common.event.Event;
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

    public EnrollmentAggregate(String aggregateId) {
        super(aggregateId);
    }

    @Override
    public void transform(Event event) {
        ObjectMapper om = new ObjectMapper();
        switch(event.getEventName()) {

        }
    }
}

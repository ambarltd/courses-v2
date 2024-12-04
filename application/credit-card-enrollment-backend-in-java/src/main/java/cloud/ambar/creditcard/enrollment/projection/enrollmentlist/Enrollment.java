package cloud.ambar.creditcard.enrollment.projection.enrollmentlist;

import jakarta.persistence.Id;
import lombok.*;
import org.springframework.data.mongodb.core.index.Indexed;

import java.time.Instant;

@Builder
@AllArgsConstructor
@Getter
@Setter
public class Enrollment {
    @Id @NonNull private String id;
    @Indexed @NonNull private String userId;
    @NonNull private String productId;
    @NonNull private Instant requestedDate;
    @NonNull private String status;
    private Instant reviewedDate;
}

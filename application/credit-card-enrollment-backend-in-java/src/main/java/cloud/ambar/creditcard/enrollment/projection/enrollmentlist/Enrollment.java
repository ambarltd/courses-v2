package cloud.ambar.creditcard.enrollment.projection.enrollmentlist;

import jakarta.persistence.Id;
import lombok.*;
import org.springframework.data.mongodb.core.index.Indexed;
import org.springframework.data.mongodb.core.mapping.Document;

import java.time.Instant;

@Builder
@AllArgsConstructor
@Getter
@Setter
@Document(collection = "CreditCard_Enrollment_Enrollment")
public class Enrollment {
    @Id @NonNull private String id;
    @Indexed @NonNull private String userId;
    @NonNull private String productId;
    @NonNull private Instant requestedDate;
    @NonNull private String status;
    private Instant reviewedDate;
}

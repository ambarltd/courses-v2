package cloud.ambar.creditcard.enrollment.projection.enrollmentlist;

import jakarta.persistence.Id;
import lombok.*;
import org.springframework.data.mongodb.core.mapping.Document;

@Builder
@AllArgsConstructor
@Getter
@Setter
public class ProductName {
    @Id @NonNull private String id;
    @NonNull private String name;
}

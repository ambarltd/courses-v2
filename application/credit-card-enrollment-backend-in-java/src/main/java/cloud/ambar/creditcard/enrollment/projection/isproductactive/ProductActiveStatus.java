package cloud.ambar.creditcard.enrollment.projection.isproductactive;

import jakarta.persistence.Id;
import lombok.*;

@Builder
@AllArgsConstructor
@Getter
@Setter
public class ProductActiveStatus {
    @Id
    @NonNull private String id;
    @NonNull private Boolean active;
}
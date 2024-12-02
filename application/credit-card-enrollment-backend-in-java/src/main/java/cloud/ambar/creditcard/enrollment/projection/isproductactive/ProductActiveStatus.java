package cloud.ambar.creditcard.enrollment.projection.isproductactive;

import jakarta.persistence.Id;
import lombok.*;
import org.springframework.data.mongodb.core.mapping.Document;

@Builder
@AllArgsConstructor
@Getter
@Setter
@Document(collection = "CreditCard_Enrollment_ProductActiveStatus")
public class ProductActiveStatus {
    @Id
    @NonNull private String id;
    @NonNull private Boolean active;
}
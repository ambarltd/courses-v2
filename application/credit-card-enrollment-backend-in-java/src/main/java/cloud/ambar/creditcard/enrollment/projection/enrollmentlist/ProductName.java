package cloud.ambar.creditcard.enrollment.projection.enrollmentlist;

import jakarta.persistence.Id;
import lombok.*;
import org.springframework.data.mongodb.core.mapping.Document;

@Builder
@AllArgsConstructor
@Getter
@Setter
@Document(collection = "CreditCard_Enrollment_ProductName")
public class ProductName {
    @Id @NonNull private String id;
    @NonNull private String name;
}

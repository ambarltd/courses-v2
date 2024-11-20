package cloud.ambar.product.enrollment.projection.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import jakarta.persistence.Id;
import lombok.Data;
import lombok.NoArgsConstructor;
import org.springframework.data.mongodb.core.index.Indexed;
import org.springframework.data.mongodb.core.mapping.Document;

@Data
@NoArgsConstructor
@Document(collection = "Enrollments")
@JsonIgnoreProperties(ignoreUnknown = true)
public class EnrollmentRequest {
    @Id
    private String id;
    @Indexed
    private String userId;
    @Indexed
    private String productId;
    private String productName;
    private String requestedDate;
    @Indexed
    private String status;
    private String statusCode;
    private String statusReason;
    private String reviewedDate;
}

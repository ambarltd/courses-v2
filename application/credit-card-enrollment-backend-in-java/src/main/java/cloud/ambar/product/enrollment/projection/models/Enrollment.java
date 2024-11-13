package cloud.ambar.product.enrollment.projection.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import jakarta.persistence.Id;
import lombok.Data;
import lombok.NoArgsConstructor;
import org.springframework.data.mongodb.core.mapping.Document;

@Data
@NoArgsConstructor
@Document(collection = "Enrollments")
@JsonIgnoreProperties(ignoreUnknown = true)
public class Enrollment {
    @Id
    private String id;
    private String requestedDate;
    private String status;
    private String reviewedDate;
}

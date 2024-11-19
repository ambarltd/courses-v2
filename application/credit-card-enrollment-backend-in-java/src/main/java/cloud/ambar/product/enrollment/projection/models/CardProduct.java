package cloud.ambar.product.enrollment.projection.models;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;
import jakarta.persistence.Id;
import lombok.Data;
import lombok.NoArgsConstructor;
import org.springframework.data.mongodb.core.mapping.Document;

/**
 * Whaaaat, why do we have another CardProduct projection model?!?
 * Well, because the enrollment will have its own projection for just the properties of card products it cares about.
 * This is how we can create vertical isolation among the features and aspects of our code base.
 */
@Data
@NoArgsConstructor
@Document(collection = "EnrollmentProducts")
@JsonIgnoreProperties(ignoreUnknown = true)
public class CardProduct {
    @Id
    private String id;
    private boolean active;
    private String name;
}
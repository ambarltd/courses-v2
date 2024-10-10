package cloud.ambar.creditCardProduct.data.models.projection;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;
import jakarta.persistence.Id;
import lombok.Data;
import lombok.NoArgsConstructor;
import org.springframework.data.mongodb.core.mapping.Document;

@Data
@NoArgsConstructor
@Document(collection = "ProductListItems")
@JsonIgnoreProperties(ignoreUnknown = true)
public class Product {
    @Id
    private String id;
    @JsonProperty("aggregate_id")
    private String aggregateId;
    private String name;
    private boolean active;
}

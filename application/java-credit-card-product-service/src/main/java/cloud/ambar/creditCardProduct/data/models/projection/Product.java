package cloud.ambar.creditCardProduct.data.models.projection;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;
import jakarta.persistence.Column;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import lombok.AllArgsConstructor;
import lombok.Data;
import org.springframework.data.mongodb.core.mapping.Document;

@Data
@AllArgsConstructor
@Document(collection = "ProductListItems")
@Table(name = "ProductListItems")
@JsonIgnoreProperties(ignoreUnknown = true)
public class Product {
    @Id
    @JsonProperty("aggregate_id")
    private String id;
    @Column(name = "name")
    private String name;
    @Column(name = "active")
    private boolean active;
}

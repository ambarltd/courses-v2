package cloud.ambar.creditCardProduct.models.projection;

import jakarta.persistence.Id;
import lombok.AllArgsConstructor;
import lombok.Data;
import org.springframework.data.mongodb.core.mapping.Document;

@Data
@AllArgsConstructor
@Document(collection = "ProductListItems")
public class ProductListItem {
    @Id
    private String id;
    private String name;
    private boolean active;
}

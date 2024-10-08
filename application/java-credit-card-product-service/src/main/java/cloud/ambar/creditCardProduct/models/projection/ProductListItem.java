package cloud.ambar.creditCardProduct.models.projection;

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
public class ProductListItem {
    @Id
    private String id;
    @Column(name = "name")
    private String name;
    @Column(name = "active")
    private boolean active;
}

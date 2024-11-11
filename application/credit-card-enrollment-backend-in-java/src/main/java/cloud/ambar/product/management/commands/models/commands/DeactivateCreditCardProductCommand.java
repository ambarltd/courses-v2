package cloud.ambar.product.management.commands.models.commands;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class DeactivateCreditCardProductCommand {
    private String id;
}

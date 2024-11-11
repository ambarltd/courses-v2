package cloud.ambar.product.management.commands.models;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class ModifyCreditCardCommand {
    private String id;
    private int annualFeeInCents;
    private String paymentCycle;
    private int creditLimitInCents;
    private String cardBackgroundHex;
}

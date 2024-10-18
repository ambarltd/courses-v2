package cloud.ambar.creditCardProduct.command.models.commands;

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

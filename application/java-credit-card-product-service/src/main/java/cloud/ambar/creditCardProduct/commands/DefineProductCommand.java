package cloud.ambar.creditCardProduct.commands;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;


@Data
@NoArgsConstructor
@AllArgsConstructor
public class DefineProductCommand {
    private String productIdentifierForAggregateIdHash;
    private String name;
    private int interestInBasisPoints;
    private int annualFeeInCents;
    private String paymentCycle;
    private int creditLimitInCents;
    private int maxBalanceTransferAllowedInCents;
    private String reward;
    private String cardBackgroundHex;
}

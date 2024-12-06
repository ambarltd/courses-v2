package cloud.ambar.creditcard.product.event;

import cloud.ambar.common.event.Event;
import lombok.Getter;
import lombok.NonNull;
import lombok.experimental.SuperBuilder;

@SuperBuilder
@Getter
public class ProductDefined extends Event {
    @NonNull private String name;
    @NonNull private Integer interestInBasisPoints;
    @NonNull private Integer annualFeeInCents;
    @NonNull private String paymentCycle;
    @NonNull private Integer creditLimitInCents;
    @NonNull private Integer maxBalanceTransferAllowedInCents;
    @NonNull private String reward;
    @NonNull private String cardBackgroundHex;
}

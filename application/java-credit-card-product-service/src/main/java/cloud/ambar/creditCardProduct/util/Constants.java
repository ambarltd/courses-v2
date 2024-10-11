package cloud.ambar.creditCardProduct.util;

import cloud.ambar.creditCardProduct.command.models.commands.DefineCreditCardProductCommand;
import cloud.ambar.creditCardProduct.command.models.validation.PaymentCycle;
import cloud.ambar.creditCardProduct.command.models.validation.RewardsType;

public class Constants {

    public static final DefineCreditCardProductCommand STARTER = DefineCreditCardProductCommand.builder()
            .productIdentifierForAggregateIdHash("STARTER_CREDIT_CARD")
            .name("Starter")
            .interestInBasisPoints(1200)
            .annualFeeInCents(5000)
            .paymentCycle(PaymentCycle.MONTHLY.name())
            .creditLimitInCents(50000)
            .maxBalanceTransferAllowedInCents(0)
            .reward(RewardsType.NONE.name())
            .cardBackgroundHex("#7fffd4")
            .build();

    public static final DefineCreditCardProductCommand BASIC = DefineCreditCardProductCommand.builder()
            .productIdentifierForAggregateIdHash("BASIC_CREDIT_CARD")
            .name("Basic")
            .interestInBasisPoints(1500)
            .annualFeeInCents(7500)
            .paymentCycle(PaymentCycle.MONTHLY.name())
            .creditLimitInCents(500000)
            .maxBalanceTransferAllowedInCents(100000)
            .reward(RewardsType.NONE.name())
            .cardBackgroundHex("#34eb37")
            .build();

    public static final DefineCreditCardProductCommand BASIC_CASH_BACK = DefineCreditCardProductCommand.builder()
            .productIdentifierForAggregateIdHash("BASIC_CASH_BACK_CREDIT_CARD")
            .name("Cash Back - Basic")
            .interestInBasisPoints(2000)
            .annualFeeInCents(8500)
            .paymentCycle(PaymentCycle.MONTHLY.name())
            .creditLimitInCents(500000)
            .maxBalanceTransferAllowedInCents(100000)
            .reward(RewardsType.CASHBACK.name())
            .cardBackgroundHex("#e396ff")
            .build();

    public static final DefineCreditCardProductCommand BASIC_POINTS = DefineCreditCardProductCommand.builder()
            .productIdentifierForAggregateIdHash("BASIC_POINTS_CREDIT_CARD")
            .name("Travel Points - Basic")
            .interestInBasisPoints(2000)
            .annualFeeInCents(8500)
            .paymentCycle(PaymentCycle.MONTHLY.name())
            .creditLimitInCents(500000)
            .maxBalanceTransferAllowedInCents(100000)
            .reward(RewardsType.POINTS.name())
            .cardBackgroundHex("#3a34eb")
            .build();

    public static final DefineCreditCardProductCommand PLATINUM = DefineCreditCardProductCommand.builder()
            .productIdentifierForAggregateIdHash("PLATINUM_CREDIT_CARD")
            .name("Platinum")
            .interestInBasisPoints(300)
            .annualFeeInCents(50000)
            .paymentCycle(PaymentCycle.QUARTERLY.name())
            .creditLimitInCents(5000000)
            .maxBalanceTransferAllowedInCents(100000)
            .reward(RewardsType.POINTS.name())
            .cardBackgroundHex("#E5E4E2")
            .build();
}

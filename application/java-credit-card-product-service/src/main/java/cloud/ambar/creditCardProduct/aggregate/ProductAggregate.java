package cloud.ambar.creditCardProduct.aggregate;

import cloud.ambar.common.models.AggregateTraits;
import cloud.ambar.common.models.Event;
import cloud.ambar.creditCardProduct.events.ProductActivatedEvent;
import cloud.ambar.creditCardProduct.events.ProductDeactivatedEvent;
import cloud.ambar.creditCardProduct.events.ProductDefinedEvent;
import cloud.ambar.creditCardProduct.data.models.PaymentCycle;
import cloud.ambar.creditCardProduct.data.models.RewardsType;
import lombok.Builder;
import lombok.Getter;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

@Getter
public class ProductAggregate extends AggregateTraits {
    private static final Logger log = LogManager.getLogger(ProductAggregate.class);
    private final String name;
    private final int interestInBasisPoints;
    private final int annualFeeInCents;
    private final PaymentCycle paymentCycle;
    private final int creditLimitInCents;
    private final int maxBalanceTransferAllowedInCents;
    private final RewardsType reward;
    private final String cardBackgroundHex;
    private final Boolean active;

    @Builder
    public ProductAggregate(String aggregateId, long aggregateVersion, String name, int interestInBasisPoints, int annualFeeInCents, PaymentCycle paymentCycle, int creditLimitInCents, int maxBalanceTransferAllowedInCents, RewardsType reward, String cardBackgroundHex, boolean active) {
        super(aggregateId, aggregateVersion);
        this.name = name;
        this.interestInBasisPoints = interestInBasisPoints;
        this.annualFeeInCents = annualFeeInCents;
        this.paymentCycle = paymentCycle;
        this.creditLimitInCents = creditLimitInCents;
        this.maxBalanceTransferAllowedInCents = maxBalanceTransferAllowedInCents;
        this.reward = reward;
        this.cardBackgroundHex = cardBackgroundHex;
        this.active = active;
    }

    @Override
    public void transform(Event event) {
        switch(event.getEventName()) {
            case ProductDefinedEvent.EVENT_NAME -> log.info("Got ProductDefinedEvent");
            case ProductActivatedEvent.EVENT_NAME -> log.info("Got ProductActivatedEvent");
            case ProductDeactivatedEvent.EVENT_NAME -> log.info("Got ProductDeactivatedEvent");
        }
    }
}

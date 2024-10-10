package cloud.ambar.creditCardProduct.aggregate;

import cloud.ambar.creditCardProduct.events.Event;
import cloud.ambar.creditCardProduct.events.ProductActivatedEvent;
import cloud.ambar.creditCardProduct.events.ProductDeactivatedEvent;
import cloud.ambar.creditCardProduct.events.ProductDefinedEvent;
import cloud.ambar.creditCardProduct.data.models.PaymentCycle;
import cloud.ambar.creditCardProduct.data.models.RewardsType;
import cloud.ambar.creditCardProduct.exceptions.InvalidEventException;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.Builder;
import lombok.Data;
import lombok.EqualsAndHashCode;
import lombok.Getter;
import lombok.NoArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

@EqualsAndHashCode(callSuper = true)
@Data
@NoArgsConstructor
public class ProductAggregate extends AggregateTraits {
    private static final Logger log = LogManager.getLogger(ProductAggregate.class);
    private String name;
    private int interestInBasisPoints;
    private int annualFeeInCents;
    private PaymentCycle paymentCycle;
    private int creditLimitInCents;
    private int maxBalanceTransferAllowedInCents;
    private RewardsType reward;
    private String cardBackgroundHex;
    private boolean active;

    @Override
    public void transform(Event event) {
        switch(event.getEventName()) {
            case ProductDefinedEvent.EVENT_NAME -> {
                log.info("Transforming aggregate for ProductDefinedEvent");
                ObjectMapper om = new ObjectMapper();
                try {
                    ProductAggregate p = om.readValue(event.getData(), ProductAggregate.class);
                    this.setAggregateId(p.getAggregateId());
                    this.setAggregateVersion(p.getAggregateVersion());
                    this.setName(p.getName());
                    this.setInterestInBasisPoints(p.getInterestInBasisPoints());
                    this.setAnnualFeeInCents(p.getAnnualFeeInCents());
                    this.setPaymentCycle(p.getPaymentCycle());
                    this.setCreditLimitInCents(p.getCreditLimitInCents());
                    this.setMaxBalanceTransferAllowedInCents(p.getMaxBalanceTransferAllowedInCents());
                    this.setReward(p.getReward());
                    this.setCardBackgroundHex(p.getCardBackgroundHex());
                    this.setActive(false);
                } catch (JsonProcessingException e) {
                    throw new InvalidEventException("Error processing ProductDefinedEvent");
                }
            }
            case ProductActivatedEvent.EVENT_NAME -> {
                log.info("Transforming aggregate for ProductActivatedEvent");
                this.active = true;
            }
            case ProductDeactivatedEvent.EVENT_NAME -> {
                log.info("Transforming aggregate for ProductDeactivatedEvent");
                this.active = false;
            }
        }
    }
}

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
    private String paymentCycle;
    private int creditLimitInCents;
    private int maxBalanceTransferAllowedInCents;
    private String reward;
    private String cardBackgroundHex;
    private boolean active;

    public ProductAggregate(String aggregateId, long aggregateVersion) {
        super(aggregateId, aggregateVersion);
    }

    @Override
    public void transform(Event event) {
        switch(event.getEventName()) {
            case ProductDefinedEvent.EVENT_NAME -> {
                log.info("Transforming aggregate for ProductDefinedEvent");
                ObjectMapper om = new ObjectMapper();
                try {
                    ProductDefinedEvent definition = om.readValue(event.getData(), ProductDefinedEvent.class);
                    this.setAggregateId(event.getAggregateId());
                    this.setAggregateVersion(event.getVersion());
                    this.setName(definition.getName());
                    this.setInterestInBasisPoints(definition.getInterestInBasisPoints());
                    this.setAnnualFeeInCents(definition.getAnnualFeeInCents());
                    this.setPaymentCycle(definition.getPaymentCycle());
                    this.setCreditLimitInCents(definition.getCreditLimitInCents());
                    this.setMaxBalanceTransferAllowedInCents(definition.getMaxBalanceTransferAllowedInCents());
                    this.setReward(definition.getReward());
                    this.setCardBackgroundHex(definition.getCardBackgroundHex());
                    this.setActive(false);
                } catch (JsonProcessingException e) {
                    log.error("Error creating initial product definition from event!");
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

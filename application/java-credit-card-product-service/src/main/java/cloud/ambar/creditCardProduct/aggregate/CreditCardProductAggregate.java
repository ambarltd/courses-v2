package cloud.ambar.creditCardProduct.aggregate;

import cloud.ambar.creditCardProduct.events.Event;
import cloud.ambar.creditCardProduct.events.ProductActivatedEventData;
import cloud.ambar.creditCardProduct.events.ProductDeactivatedEventData;
import cloud.ambar.creditCardProduct.events.ProductDefinedEventData;
import cloud.ambar.creditCardProduct.exceptions.InvalidEventException;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.Data;
import lombok.EqualsAndHashCode;
import lombok.NoArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

@EqualsAndHashCode(callSuper = true)
@Data
@NoArgsConstructor
public class CreditCardProductAggregate extends Aggregate {
    private static final Logger log = LogManager.getLogger(CreditCardProductAggregate.class);
    private String name;
    private int interestInBasisPoints;
    private int annualFeeInCents;
    private String paymentCycle;
    private int creditLimitInCents;
    private int maxBalanceTransferAllowedInCents;
    private String reward;
    private String cardBackgroundHex;
    private boolean active;

    public CreditCardProductAggregate(String aggregateId) {
        super(aggregateId);
    }

    @Override
    public void transform(Event event) {
        switch(event.getEventName()) {
            case ProductDefinedEventData.EVENT_NAME -> {
                log.info("Transforming aggregate for ProductDefinedEvent");
                ObjectMapper om = new ObjectMapper();
                try {
                    ProductDefinedEventData definition = om.readValue(event.getData(), ProductDefinedEventData.class);
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
                    throw new RuntimeException("Error processing ProductDefinedEvent");
                }
            }
            case ProductActivatedEventData.EVENT_NAME -> {
                log.info("Transforming aggregate for ProductActivatedEvent");
                this.active = true;
            }
            case ProductDeactivatedEventData.EVENT_NAME -> {
                log.info("Transforming aggregate for ProductDeactivatedEvent");
                this.active = false;
            }
        }
    }
}

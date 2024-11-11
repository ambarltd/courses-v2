package cloud.ambar.product.management.aggregate;

import cloud.ambar.common.aggregate.Aggregate;
import cloud.ambar.common.event.models.Event;
import cloud.ambar.product.management.events.ProductActivatedEventData;
import cloud.ambar.product.management.events.ProductAnnualFeeChangedEventData;
import cloud.ambar.product.management.events.ProductBackgroundChangedEventData;
import cloud.ambar.product.management.events.ProductCreditLimitChangedEventData;
import cloud.ambar.product.management.events.ProductDeactivatedEventData;
import cloud.ambar.product.management.events.ProductDefinedEventData;
import cloud.ambar.product.management.events.ProductPaymentCycleChangedEventData;
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
        ObjectMapper om = new ObjectMapper();
        switch(event.getEventName()) {
            case ProductDefinedEventData.EVENT_NAME -> {
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
                this.active = true;
            }
            case ProductDeactivatedEventData.EVENT_NAME -> {
                this.active = false;
            }
            case ProductAnnualFeeChangedEventData.EVENT_NAME -> {
                try {
                    ProductAnnualFeeChangedEventData modification = om.readValue(event.getData(), ProductAnnualFeeChangedEventData.class);
                    this.setAnnualFeeInCents(modification.getAnnualFeeInCents());
                } catch (JsonProcessingException e) {
                    throw new RuntimeException(e.getMessage());
                }
            }
            case ProductPaymentCycleChangedEventData.EVENT_NAME -> {
                try {
                    ProductPaymentCycleChangedEventData modification = om.readValue(event.getData(), ProductPaymentCycleChangedEventData.class);
                    this.setPaymentCycle(modification.getPaymentCycle());
                } catch (JsonProcessingException e) {
                    throw new RuntimeException(e.getMessage());
                }
            }
            case ProductCreditLimitChangedEventData.EVENT_NAME -> {
                try {
                    ProductCreditLimitChangedEventData modification = om.readValue(event.getData(), ProductCreditLimitChangedEventData.class);
                    this.setCreditLimitInCents(modification.getCreditLimitInCents());
                } catch (JsonProcessingException e) {
                    throw new RuntimeException(e.getMessage());
                }
            }
            case ProductBackgroundChangedEventData.EVENT_NAME -> {
                try {
                    ProductBackgroundChangedEventData modification = om.readValue(event.getData(), ProductBackgroundChangedEventData.class);
                    this.setCardBackgroundHex(modification.getCardBackgroundHex());
                } catch (JsonProcessingException e) {
                    throw new RuntimeException(e.getMessage());
                }
            }
        }
    }
}

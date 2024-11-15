package cloud.ambar.product.management.events;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;


@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
@JsonIgnoreProperties(ignoreUnknown = true)
public class ProductDefinedEventData {
    public static final String EVENT_NAME = "CreditCardProduct_Product_ProductDefined";

    // Below are the product details for the event, to be returned as the serialized data of the event
    private String name;
    private int interestInBasisPoints;
    private int annualFeeInCents;
    private String paymentCycle;
    private int creditLimitInCents;
    private int maxBalanceTransferAllowedInCents;
    private String reward;
    private String cardBackgroundHex;
}

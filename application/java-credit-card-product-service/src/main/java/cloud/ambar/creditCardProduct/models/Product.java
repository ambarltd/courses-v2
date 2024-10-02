package cloud.ambar.creditCardProduct.models;

import cloud.ambar.creditCardProduct.aggregate.ProductAggregate;
import lombok.Builder;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

@Builder
public class Product {
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
}
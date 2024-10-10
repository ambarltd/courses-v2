package cloud.ambar.creditCardProduct.exceptions;

import lombok.NoArgsConstructor;

@NoArgsConstructor
public class InvalidPaymentCycleException extends RuntimeException {
    public InvalidPaymentCycleException(String msg) {
        super(msg);
    }
}

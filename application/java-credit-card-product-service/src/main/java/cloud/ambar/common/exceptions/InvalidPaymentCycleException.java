package cloud.ambar.common.exceptions;

import lombok.NoArgsConstructor;

@NoArgsConstructor
public class InvalidPaymentCycleException extends RuntimeException {
    public InvalidPaymentCycleException(String msg) {
        super(msg);
    }
}

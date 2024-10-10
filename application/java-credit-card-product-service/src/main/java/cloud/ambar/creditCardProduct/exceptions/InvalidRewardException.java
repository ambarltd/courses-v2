package cloud.ambar.creditCardProduct.exceptions;

import lombok.NoArgsConstructor;

@NoArgsConstructor
public class InvalidRewardException extends RuntimeException {
    public InvalidRewardException(String msg) {
        super(msg);
    }
}

package cloud.ambar.creditCardProduct.exceptions;

import lombok.NoArgsConstructor;

@NoArgsConstructor
public class InvalidEventException extends RuntimeException {
    public InvalidEventException(String msg) {
        super(msg);
    }
}

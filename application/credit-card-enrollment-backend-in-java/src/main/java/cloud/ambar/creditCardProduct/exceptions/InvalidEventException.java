package cloud.ambar.creditCardProduct.exceptions;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(value = HttpStatus.BAD_REQUEST, reason = "Request conflicts with current state of Credit Card Product(s)")
public class InvalidEventException extends RuntimeException {
}

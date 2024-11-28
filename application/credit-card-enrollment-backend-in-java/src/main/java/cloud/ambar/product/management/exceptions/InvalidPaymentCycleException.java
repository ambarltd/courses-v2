package cloud.ambar.product.management.exceptions;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(value = HttpStatus.BAD_REQUEST, reason = "Invalid Payment Cycle")
public class InvalidPaymentCycleException extends RuntimeException {
}

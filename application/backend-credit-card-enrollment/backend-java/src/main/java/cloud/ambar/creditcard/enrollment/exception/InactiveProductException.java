package cloud.ambar.creditcard.enrollment.exception;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(value = HttpStatus.BAD_REQUEST, reason = "Product is inactive and not eligible for the request.")
public class InactiveProductException extends RuntimeException {
}

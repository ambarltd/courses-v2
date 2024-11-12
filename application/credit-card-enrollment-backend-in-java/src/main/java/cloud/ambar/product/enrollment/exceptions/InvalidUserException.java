package cloud.ambar.product.enrollment.exceptions;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(value = HttpStatus.BAD_REQUEST, reason = "Invalid UserId")
public class InvalidUserException extends RuntimeException {
}
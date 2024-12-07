package cloud.ambar.creditcard.enrollment.exception;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(value = HttpStatus.BAD_REQUEST, reason = "InvalidSessionToken")
public class InvalidSessionTokenException extends RuntimeException {
}
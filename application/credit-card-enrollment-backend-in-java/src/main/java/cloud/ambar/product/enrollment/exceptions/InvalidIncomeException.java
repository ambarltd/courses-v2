package cloud.ambar.product.enrollment.exceptions;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(value = HttpStatus.BAD_REQUEST, reason = "Zero or negative value passed")
public class InvalidIncomeException extends RuntimeException {
}

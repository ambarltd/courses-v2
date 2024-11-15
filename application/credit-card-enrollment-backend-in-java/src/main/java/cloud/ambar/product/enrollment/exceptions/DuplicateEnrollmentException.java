package cloud.ambar.product.enrollment.exceptions;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(value = HttpStatus.CONFLICT, reason = "Enrollment for product by user already present.")
public class DuplicateEnrollmentException extends RuntimeException {
}

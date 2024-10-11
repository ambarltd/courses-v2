package cloud.ambar.creditCardProduct.exceptions;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(value = HttpStatus.NOT_FOUND, reason = "No matching Product found")
public class NoSuchProductException extends RuntimeException {
}

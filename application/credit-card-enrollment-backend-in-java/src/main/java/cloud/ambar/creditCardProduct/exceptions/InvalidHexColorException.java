package cloud.ambar.creditCardProduct.exceptions;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(value = HttpStatus.BAD_REQUEST, reason = "Invalid Background Color specific")
public class InvalidHexColorException extends RuntimeException {}

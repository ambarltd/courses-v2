package cloud.ambar.creditCardProduct.exceptions;

public class NoSuchProductException extends RuntimeException {
    public NoSuchProductException(String msg) {
        super(msg);
    }
}

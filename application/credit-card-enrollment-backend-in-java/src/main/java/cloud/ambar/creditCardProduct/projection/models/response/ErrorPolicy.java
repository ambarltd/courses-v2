package cloud.ambar.creditCardProduct.projection.models.response;

public enum ErrorPolicy {
    KEEP_GOING,
    MUST_RETRY;

    public String toString() {
        return name().toLowerCase();
    }
}

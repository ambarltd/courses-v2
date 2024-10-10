package cloud.ambar.creditCardProduct.data.models.ambar;

public enum ErrorPolicy {
    KEEP_GOING,
    MUST_RETRY;

    public String toString() {
        return name().toLowerCase();
    }
}

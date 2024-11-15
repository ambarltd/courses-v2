package cloud.ambar.common.ambar.response;

public enum ErrorPolicy {
    KEEP_GOING,
    MUST_RETRY;

    public String toString() {
        return name().toLowerCase();
    }
}

package cloud.ambar.common.models;

public interface Aggregate {
    void transform(final Event event);
}

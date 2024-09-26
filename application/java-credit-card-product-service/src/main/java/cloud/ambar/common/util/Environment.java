package cloud.ambar.common.util;

public class Environment {
    public static String getEnvironmentVar(final String key) {
        return System.getenv(key);
    }
}

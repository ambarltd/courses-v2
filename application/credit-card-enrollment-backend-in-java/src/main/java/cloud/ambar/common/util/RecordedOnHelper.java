package cloud.ambar.common.util;

import java.time.OffsetDateTime;

public class RecordedOnHelper {
    public static String recordedNow() {
        final String time = OffsetDateTime.now().toString();
        return time.substring(0, 26).replace("T", " ") + " UTC";
    }
}

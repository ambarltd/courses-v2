package cloud.ambar.common.util;

import com.fasterxml.jackson.core.JsonGenerator;
import com.fasterxml.jackson.databind.JsonSerializer;
import com.fasterxml.jackson.databind.SerializerProvider;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import java.io.IOException;
import java.time.OffsetDateTime;

public class OffsetDateTimeSerializer extends JsonSerializer<OffsetDateTime> {
    private static final Logger log = LogManager.getLogger(OffsetDateTimeSerializer.class);

    @Override
    public void serialize(OffsetDateTime value, JsonGenerator gen, SerializerProvider serializers) throws IOException {
        final String after = value.toString()
                .replace("+00", " UTC");
        gen.writeString(after);
    }
}

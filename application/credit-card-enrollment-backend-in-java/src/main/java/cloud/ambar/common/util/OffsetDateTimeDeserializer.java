package cloud.ambar.common.util;

import com.fasterxml.jackson.core.JsonParser;
import com.fasterxml.jackson.databind.DeserializationContext;
import com.fasterxml.jackson.databind.JsonDeserializer;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import java.io.IOException;
import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;

public class OffsetDateTimeDeserializer extends JsonDeserializer<OffsetDateTime> {
    private static final Logger log = LogManager.getLogger(OffsetDateTimeDeserializer.class);


    @Override
    public OffsetDateTime deserialize(JsonParser p, DeserializationContext ctxt) throws IOException {
        String rawTimestamp = p.getText();
        String adjustedTimestamp = rawTimestamp
                .replace(" UTC", "+00")
                .replace(" ", "T");
        return OffsetDateTime.parse(adjustedTimestamp);
    }
}


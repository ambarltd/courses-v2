package cloud.ambar.common.sessionauth;

import lombok.Getter;
import org.springframework.context.annotation.Configuration;
import org.springframework.beans.factory.annotation.Value;

@Configuration
@Getter
public class SessionConfig {
    @Value("${app.session.session_tokens_expire_after_seconds}")
    private int sessionTokensExpireAfterSeconds;
}

package cloud.ambar.common.sessionauth;

import jakarta.persistence.Id;
import lombok.*;

import java.time.Instant;

@Builder
@AllArgsConstructor
@Getter
public class Session {
    @Id @NonNull private String id;
    @NonNull private String userId;
    @NonNull private String sessionToken;
    @NonNull private Boolean signedOut;
    @NonNull private Instant tokenLastRefreshedAt;
}
